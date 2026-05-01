<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.method' => \App\Http\Middleware\RequiresAuthMethod::class,
        ]);
        $middleware->validateCsrfTokens(except: ['/github/webhook', '/preview/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null; // let Laravel handle 404/403/etc and API errors normally
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: get_class($e));
        });
    })->create();
