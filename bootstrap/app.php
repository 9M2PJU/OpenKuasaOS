<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Http\Request;

if (class_exists(ServeCommand::class)) {
    if (! in_array('PHPRC', ServeCommand::$passthroughVariables, true)) {
        ServeCommand::$passthroughVariables[] = 'PHPRC';
    }
    if (! in_array('PHP_INI_SCAN_DIR', ServeCommand::$passthroughVariables, true)) {
        ServeCommand::$passthroughVariables[] = 'PHP_INI_SCAN_DIR';
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'tenant' => \App\Http\Middleware\SetCurrentOrganization::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperadmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_FAILED',
                        'message' => 'Data yang dimasukkan tidak sah.',
                        'details' => $e->errors(),
                    ],
                ], 422);
            }
        });
    })->create();
