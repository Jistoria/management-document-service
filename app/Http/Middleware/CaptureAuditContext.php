<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CaptureAuditContext
{
    /**
     * Handle an incoming request para capturar contexto de auditoría.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Capturar información del usuario desde headers de microservicio
        $this->captureUserContextFromHeaders($request);

        // Generar correlation ID si no existe
        $this->ensureCorrelationId($request);

        return $next($request);
    }

    /**
     * Capturar contexto del usuario desde headers
     */
    protected function captureUserContextFromHeaders(Request $request): void
    {
        // Si viene de un microservicio de autenticación
        if ($request->hasHeader('X-User-Id')) {
            $userData = [
                'id' => $request->header('X-User-Id'),
                'external_id' => $request->header('X-External-User-Id', $request->header('X-User-Id')),
                'email' => $request->header('X-User-Email'),
                'name' => $request->header('X-User-Name'),
                'roles' => json_decode($request->header('X-User-Roles', '[]'), true),
            ];

            // Crear un usuario temporal para el contexto de auditoría
            $this->setTemporaryUser($userData);
        }

        // Merge additional context into request
        $request->merge([
            'audit_context' => [
                'correlation_id' => $request->header('X-Correlation-ID'),
                'request_id' => $request->header('X-Request-ID'),
                'service_source' => $request->header('X-Service-Source'),
                'client_version' => $request->header('X-Client-Version'),
            ]
        ]);
    }

    /**
     * Establecer usuario temporal para auditoría
     */
    protected function setTemporaryUser(array $userData): void
    {
        if (!Auth::check() && !empty($userData['id'])) {
            // Crear un objeto user temporal para auditoría
            $tempUser = new class($userData) {
                private $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function __get($key)
                {
                    return $this->data[$key] ?? null;
                }

                public function getAuthIdentifier()
                {
                    return $this->data['id'];
                }
            };

            // No usar Auth::setUser() para evitar conflictos
            // En su lugar, podemos usar el request para pasarlo al trait
            request()->merge(['audit_user' => $tempUser]);
        }
    }

    /**
     * Asegurar que existe un correlation ID
     */
    protected function ensureCorrelationId(Request $request): void
    {
        if (!$request->hasHeader('X-Correlation-ID')) {
            $correlationId = $this->generateCorrelationId();
            $request->headers->set('X-Correlation-ID', $correlationId);
        }
    }

    /**
     * Generar un correlation ID único
     */
    protected function generateCorrelationId(): string
    {
        return sprintf(
            '%s-%s-%s',
            config('app.name', 'mgmt-doc'),
            now()->format('YmdHis'),
            substr(md5(uniqid()), 0, 8)
        );
    }
}
