<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

function catchSync(callable $callback, string|array $message = 'Realizado con éxito', int $status = 200)
{
    try {
        return ApiResponse::success($callback(), $message, $status);
    } catch (\Throwable $e) {
        Log::error("Error: " . $e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $e->getCode());
    }
}

