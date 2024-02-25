<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function () use ($router) {
    $router->group(['prefix' => 'api'], function () use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('login', 'AuthController@login');
        $router->get('/search-all', 'CompanyController@search');
        $router->group(['middleware' => 'customRateLimit'], function () use ($router) {
            
        });

        $router->group(['middleware' => 'auth'], function () use ($router) {
            // Маршруты для пользователя
            $router->get('user', 'AuthController@user');
            $router->get('logout', 'AuthController@logout');
            $router->post('refresh', 'AuthController@refreshToken');
            $router->post('/send-verification-code', 'VerificationController@sendVerificationCode');
            $router->post('/verify-email', 'VerificationController@verifyEmail');
            $router->post('/resend-verification-code', 'VerificationController@resendVerificationCode');

            // Маршруты микросервиса компании
            $router->group(['prefix' => 'counterparty'], function () use ($router) {
                $router->get('/company/{biin}', 'CompanyController@findCompanyByBin');
            });
        });

        // Маршруты микросервиса компании
        $router->group(['prefix' => 'counterparty'], function () use ($router) {
            $router->get('company/similar-oked/{biin}', 'CompanyController@findCompanyByOked');
            $router->get('company/similar-oked-kato/{biin}', 'CompanyController@findCompanyByOkedRegion');
            $router->get('/company/similar-address/{biin}', 'CompanyController@findCompanyByAddress');
        });
    });
});
