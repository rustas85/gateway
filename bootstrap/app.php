<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));



$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();



$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);



$app->configure('app');
$app->configure('jwt');
$app->configure('swagger-lume');
$app->configure('elasticsearch');
$app->withFacades(true, [
    'Illuminate\Support\Facades\Notification' => 'Notification',
]);




$app->middleware([
    App\Http\Middleware\CorsMiddleware::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'cors' => App\Http\Middleware\CorsMiddleware::class,
    'customRateLimit' => \App\Http\Middleware\CustomRateLimitMiddleware::class
]);


$app->configure('mail');
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);


$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(MailerLite\LaravelElasticsearch\ServiceProvider::class);




$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
