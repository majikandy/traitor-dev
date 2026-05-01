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
                return null;
            }

            // Only intercept mutating requests — never GET, as redirecting a GET back to itself loops.
            if (in_array($request->method(), ['GET', 'HEAD'])) {
                return null;
            }

            try {
                $previous = url()->previous('');
                if (!$previous || $previous === $request->url()) {
                    return null;
                }

                $input = $request->except(['_token', '_method', 'password', 'password_confirmation', 'zip']);

                return redirect($previous)
                    ->withInput()
                    ->with('error', $e->getMessage() ?: get_class($e))
                    ->with('error_meta', [
                        'action' => strtoupper($request->method()) . ' /' . ltrim($request->path(), '/'),
                        'input'  => $input ?: null,
                    ]);
            } catch (\Throwable) {
                // Handler machinery failed (e.g. session not available) — fall back to Laravel's default.
                return null;
            }
        });
    })->create();
