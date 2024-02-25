<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configureApiRoutes();
    }

    private function configureApiRoutes()
    {
        Route::prefix('api')->group(function () {
            Route::post('register', 'AuthController@register');
        });
    }
}