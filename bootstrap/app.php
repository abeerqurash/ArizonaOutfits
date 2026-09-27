<?php

use App\Http\Middleware\AdminAuditMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerSecurityReminder;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Global Web Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->appendToGroup(
            'web',
            SecurityHeadersMiddleware::class
        );


        /*
        |--------------------------------------------------------------------------
        | Authentication Redirects
        |--------------------------------------------------------------------------
        |
        | Keep customer and administrator authentication completely separate.
        |
        | Guest attempting an admin page:
        |     /admin/* -> /admin/login
        |
        | Guest attempting a customer page:
        |     -> /login
        |
        | Authenticated admin attempting an admin guest page:
        |     /admin/login -> /admin/dashboard
        |
        | Authenticated customer attempting a customer guest page:
        |     -> /dashboard
        |
        */

        $middleware->redirectGuestsTo(
            function (Request $request): string {
                if (
                    $request->is('admin') ||
                    $request->is('admin/*')
                ) {
                    return route('admin.login');
                }

                return route('login');
            }
        );

        $middleware->redirectUsersTo(
            function (Request $request): string {
                if (
                    $request->is('admin') ||
                    $request->is('admin/*')
                ) {
                    return route('admin.dashboard');
                }

                return route('dashboard');
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CSRF Exceptions
        |--------------------------------------------------------------------------
        */

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'admin' => AdminMiddleware::class,

            'admin.audit' => AdminAuditMiddleware::class,

            'customer.security.reminder' => CustomerSecurityReminder::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();