<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class CustomRateLimitMiddleware
{
    public function handle($request, Closure $next)
    {
        $key = Auth::check() ? 'user-' . Auth::id() : 'ip-' . $request->ip();
        $maxAttempts = Auth::check() ? PHP_INT_MAX : 10;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilNextAttempt = RateLimiter::availableIn($key);
            $retryAfter = Carbon::now()->addSeconds($secondsUntilNextAttempt)->toIso8601String();

            $message = [
                'message' => "Слишком много запросов. Пожалуйста, подождите до $retryAfter, чтобы сделать еще один запрос. Для неограниченного доступа авторизуйтесь.",
                'retry_after' => $secondsUntilNextAttempt,
                'retry_after_human' => Carbon::now()->addSeconds($secondsUntilNextAttempt)->diffForHumans(),
            ];

            return response()->json($message, 429, [
                'Retry-After' => $secondsUntilNextAttempt,
                'X-RateLimit-Reset' => $retryAfter,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
