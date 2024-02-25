<?php
// app/Http/Controllers/VerificationController.php
namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use App\Mail\VerificationMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;


class VerificationController extends Controller
{
    public function sendVerificationCode(Request $request)
    {
        $user = Auth::user(); // Получаем авторизованного пользователя

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 404);
        }

        // Проверка, подтверждена ли уже почта
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Почта уже подтверждена.'], 400);
        }

        $verificationCode = rand(100000, 999999); // Генерация 6-значного кода

        Mail::to($user->email)->send(new VerificationMail($verificationCode));

        Cache::put('verification_code_' . $user->id, $verificationCode, 10 * 60); // Сохраняем код в кэше на 10 минут

        return response()->json(['message' => 'Код подтверждения отправлен.'], 200);
    }


    public function verifyEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден.'], 404);
        }

        $verificationCode = Cache::get('verification_code_' . $user->id);
        if (!$verificationCode) {
            return response()->json(['message' => 'Код подтверждения истек или неверен.'], 400);
        }

        if ($verificationCode == $request->code) {
            $user->email_verified_at = 1; // Email подтвержден
            $user->save();

            Cache::forget('verification_code_' . $user->id);

            return response()->json(['message' => 'Email успешно подтвержден.']);
        } else {
            return response()->json(['message' => 'Неверный код подтверждения.'], 400);
        }
    }

    public function resendVerificationCode(Request $request)
    {
        $user = Auth::user(); // Получаем авторизованного пользователя

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 404);
        }

        // Проверка, подтверждена ли уже почта
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Почта уже подтверждена.'], 400);
        }

        // Проверка на частоту запросов (пример для ограничения в 1 минуту)
        $lastSent = Cache::get('verification_code_sent_' . $user->id);
        if ($lastSent && Carbon::now()->lessThan($lastSent->addMinutes(1))) {
            return response()->json(['message' => 'Код уже был отправлен. Пожалуйста, подождите перед повторной отправкой.'], 429);
        }

        $verificationCode = rand(100000, 999999); // Генерация нового 6-значного кода

        // Отправляем письмо с использованием Mailable
        Mail::to($user->email)->send(new VerificationMail($verificationCode));

        Cache::put('verification_code_' . $user->id, $verificationCode, 10 * 60); // Сохраняем новый код в кэше на 10 минут
        Cache::put('verification_code_sent_' . $user->id, Carbon::now(), 10 * 60); // Обновляем время последней отправки

        return response()->json(['message' => 'Код подтверждения был повторно отправлен.'], 200);
    }
}
