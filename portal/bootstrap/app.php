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

            // Only intercept POST/PUT/PATCH/DELETE — GET exceptions would loop forever
            // since redirect()->back() would re-trigger the same failing GET request.
            if ($request->isMethod('GET')) {
                return null;
            }

            $previous = url()->previous();
            $current  = $request->url();

            // If there's no meaningful "back" URL, don't redirect — let Laravel show its error page
            if (!$previous || $previous === $current) {
                return null;
            }

            $input = $request->except(['_token', '_method', 'password', 'password_confirmation', 'zip']);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: get_class($e))
                ->with('error_meta', [
                    'action' => strtoupper($request->method()) . ' /' . ltrim($request->path(), '/'),
                    'input'  => $input ?: null,
                ]);
        });
    })->create();
