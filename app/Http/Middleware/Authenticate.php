<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            if (!$this->auth->guard($guard)->check()) {
                if ($request->cookie('token') != null) {
                    $request->headers->set('Authorization', 'Bearer ' . $request->cookie('token'));
                }
            }

            if (!$this->auth->guard($guard)->check()) {
                // Удаление cookie 'token'
                setcookie('token', null, time() - 3600, '/');

                return response()->json([
                    'success' => false,
                    'message' => 'Вы не авторизованы',
                ], 401);
            }

            return $next($request);
        } catch (\Exception $exception) {
            // Удаление cookie 'token'
            setcookie('token', null, time() - 3600, '/');

            return response()->json([
                'success' => false,
                'message' => 'Вы не авторизованы',
            ], 401);
        }
    }
}
