<?php

use App\Helpers\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                $model = class_basename($e->getModel());
                dd($model, $e->getModel());
                $id = implode(', ', $e->getIds() ?? []);
                return ApiResponse::error("No se encontró {$model}" . ($id ? " con ID {$id}" : ""), 404);
            }

            return null; // deja que Laravel lo maneje si no es JSON
        });
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error('Recurso no encontrado', 404);
            }
            return null;
        });
    })->create();
