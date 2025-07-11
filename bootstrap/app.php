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
        // Opcional: Middleware para convertir automáticamente todas las respuestas a camelCase
        // Descomenta la siguiente línea para habilitar conversión global
        // $middleware->api([\App\Http\Middleware\ConvertResponseToCamelCase::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
