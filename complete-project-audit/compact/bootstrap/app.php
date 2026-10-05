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
        $middleware->appendToGroup(
            'web',
            SecurityHeadersMiddleware::class
        );
        $middleware->appendToGroup('web', \App\Http\Middleware\CustomerAccountStatus::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\StorePolicyMiddleware::class);
        $middleware->redirectGuestsTo(
            function (Request $request): string {
                if (
                    $request->is('admin') ||
                    $request->is('admin
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
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