<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        //
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // Web middleware group
        ],

        'api' => [
            // API middleware group
        ],
    ];

    /**
     * The application's route middleware aliases.
     *
     * Aliases may be used instead of class names to assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'checkRole' => \App\Http\Middleware\CheckRole::class,
    ];

    protected $routeMiddleware = [
        // Route middleware
        'checkRole' => \App\Http\Middleware\CheckRole::class,
    ];
    /**
     * Register any type of the application's route middleware.
     *
     * This should be used instead of the $middleware property to customize how an application's routes are registered.
     *
     * @param  \Illuminate\Foundation\Support\Providers\RouteServiceProvider  $provider
     * @return void
     */
    protected function configureRateLimiting()
    {
        //
    }


    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function commands()
    {

        require base_path('routes/console.php');
    }
}
