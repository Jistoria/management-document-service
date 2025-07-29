<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use InvalidArgumentException;

function catchSync(callable $callback, string|array $message = 'Realizado con éxito', int $status = 200)
{
    try {
        return ApiResponse::success($callback(), $message, $status);
    } catch (ModelNotFoundException $e) {
        Log::error("Model not found: " . $e->getMessage(), ['exception' => $e]);
        $model = method_exists($e, 'getModel') ? class_basename($e->getModel()) : 'Recurso';
        $message = "$model no encontrado";
        return ApiResponse::error($message, 404);
    } catch (InvalidArgumentException $e) {
        Log::error("Invalid argument: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), 400);
    } catch (HttpException $e) {
        Log::error("HTTP exception: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $e->getCode());
    } catch (NotFoundHttpException $e) {
        Log::error("Not found HTTP exception: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error('Recurso no encontrado', 404);
    } catch (QueryException $e) {
        Log::error("Database query error: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error('Error de consulta a la base de datos.', 500);
    } catch (\Exception $e) {
        Log::error("General exception: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error('Ha ocurrido un error inesperado.', 500);
    } catch (\Throwable $e) {
        Log::error("Error: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $e->getCode());
    }
}
