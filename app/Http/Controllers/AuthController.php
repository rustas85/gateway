<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Helpers\CookieStorage;
use App\Mail\VerificationMail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{6,}$/|confirmed'
            ],
            [
                'email.required' => 'Поле :attribute обязательно к заполнению',
                'email.email' => 'Поле :attribute должно содержать действительную электронную почту',
                'email.unique' => 'Пользователь с такой почтой уже зарегистрирован',
                'password.required' => 'Поле :attribute обязательно к заполнению',
                'password.min' => 'Поле :attribute должно содержать не менее :min символов',
                'password.regex' => 'Поле :attribute должно содержать более 6 символов, цифры, строчные и заглавные буквы',
                'password.confirmed' => 'Поле :attribute не совпадает с подтверждением пароля',
            ],
            [
                'email' => 'Электронная почта',
                'password' => 'Пароль',
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'success' => false,
                'errors' => $errors,
                'code' => 400
            ], 400);
        }

        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->balance = 0;
        $user->subscription_type = 'base';
        $user->daily_requests_count = 0;
        $user->type = 'user';
        $user->save();

        try {
            // Попытка отправить код верификации
            $this->sendVerificationCodeToUser($user);
            $verificationMessage = 'Код верификации отправлен на почту.';
        } catch (\Exception $e) {
            // Обработка ошибки отправки
            $verificationMessage = 'Не удалось отправить код верификации. Пожалуйста, проверьте вашу почту позже.';
        }

        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно зарегистрирован. ' . $verificationMessage,
            'code' => 200
        ]);
    }

    protected function sendVerificationCodeToUser($user)
    {
        $verificationCode = rand(100000, 999999); // Генерация 6-значного кода

        Mail::to($user->email)->send(new VerificationMail($verificationCode));

        Cache::put('verification_code_' . $user->id, $verificationCode, 10 * 60); // Сохраняем код в кэше на 10 минут
    }

    public function login(Request $request)
    {
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'password' => 'required'
            ],
            [
                'email.required' => 'Email обязателен',
                'password.required' => 'Пароль обязателен'
            ]
        );

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Неправильный пароль или пользователь не найден', 'code' => 401], 401);
        }

        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        $refreshTokenPayload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 7 // Неделя
        ];

        $refresh_token = JWT::encode($refreshTokenPayload, env('JWT_SECRET'), 'HS256');

        if (config('app.env') != 'testing') {
            $tokenExpire = Carbon::now()->addMinutes(env('TOKEN_EXPIRE_IN', 15))->timestamp;

            Cache::put("access_token/$token", $user->id, $tokenExpire);
            Cache::put("user_id/$user->id", $token, $tokenExpire);

            $cookieExpire = $tokenExpire - time();

            $cookie = new CookieStorage();
            $cookie->set('access_token', $token, $cookieExpire);
            $cookie->set('refresh_token', $refresh_token, $cookieExpire);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'refresh_token' => $refresh_token,
                'user' => $user,
                'code' => 200
            ]
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $cookieStorage = new CookieStorage();

            $cookieStorage->delete('access_token');
            $cookieStorage->delete('refresh_token');

            Cache::forget("access_token/" . $request->bearerToken());
            Cache::forget("user_id/" . $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Успешный выход из системы',
                'code' => 200
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {

            return response()->json(
                [
                    'success' => true,
                    'data'    => $request->user(),
                    'code'  => 200
                ]
            );
        } catch (\Exception $exception) {

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'code'  => 500
                ],
                500
            );
        }
    }

    public function refreshToken(Request $request)
    {
        $user = auth()->user(); // Получаем текущего аутентифицированного пользователя

        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 30 // 30 дней
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        $refreshTokenPayload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 7 // Неделя
        ];

        $refreshToken = JWT::encode($refreshTokenPayload, env('JWT_SECRET'), 'HS256');

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'code' => 200,
            ]
        ]);
    }
}
