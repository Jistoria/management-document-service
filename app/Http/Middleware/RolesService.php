<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RolesService
{
    public function __construct(private string $requiredPermission = 'document.view') {}

    public function handle(Request $request, Closure $next, string|null $requiredPermission)
    {
        $this->requiredPermission = $requiredPermission ?? $this->requiredPermission;

        $tokenHash = $request->attributes->get('token_hash');
        $tokenType = $request->attributes->get('token_type');
        $tokenValidation = $request->attributes->get('token_validation');

        if (!$tokenHash || !$tokenType || !$tokenValidation) {
            return response()->json(['message' => 'Datos de autenticación faltantes'], 401);
        }

        // Obtener ID o CODE del microservicio desde headers
        $microId = $request->header('X-Microservice-ID');
        $microCode = $request->header('X-Microservice-Code');

        if (!$microId && !$microCode) {
            return response()->json(['message' => 'Falta X-Microservice-ID o X-Microservice-Code'], 400);
        }

        // Obtener sesión unificada desde Redis
        $sessionData = $this->getUnifiedSession($tokenHash, $tokenType);
        if (!$sessionData) {
            return response()->json(['message' => 'Sesión no encontrada'], 403);
        }

        Log::info('[RolesService] Session data retrieved', [
            'user_id' => $sessionData['user_id'] ?? 'unknown',
            'microservice_id' => $microId,
            'microservice_code' => $microCode
        ]);

        // Localizar datos del microservicio en la sesión
        $microserviceEntry = $this->findMicroserviceEntry($sessionData, $microId, $microCode);
        if (!$microserviceEntry) {
            return response()->json(['message' => 'Microservicio no autorizado en sesión'], 403);
        }

        // Validar permiso requerido
        $permissions = $microserviceEntry['permissions'] ?? [];
        if (!in_array($this->requiredPermission, $permissions, true)) {
            Log::warning('[RolesService] Permission denied', [
                'required' => $this->requiredPermission,
                'available' => $permissions,
                'user_id' => $sessionData['user_id'] ?? 'unknown'
            ]);
            return response()->json([
                'message' => 'Sin permiso para realizar esta acción',
                'required_permission' => $this->requiredPermission
            ], 403);
        }

        // Propagar datos útiles a la request
        $this->setRequestAttributes($request, $sessionData, $microserviceEntry, $microId);

        Log::info('[RolesService] Authorization successful', [
            'user_id' => $sessionData['user_id'],
            'permission' => $this->requiredPermission,
            'microservice' => $microserviceEntry['code'] ?? $microId
        ]);

        return $next($request);
    }

    /**
     * Obtiene la sesión unificada desde Redis
     */
    private function getUnifiedSession(string $tokenHash, string $tokenType): ?array
    {
        // Primero intentar obtener sesión específica del microservicio
        $sessionKey = "ms:session:{$tokenHash}";
        $sessionData = Redis::get($sessionKey);

        if ($sessionData) {
            return json_decode($sessionData, true) ?: null;
        }

        // Si no existe sesión específica, construir desde datos de validación
        return $this->buildSessionFromValidation($tokenHash, $tokenType);
    }

    /**
     * Construye datos de sesión desde la validación del token
     */
    private function buildSessionFromValidation(string $tokenHash, string $tokenType): ?array
    {
        $cacheKey = $this->getCacheKey($tokenType, $tokenHash);
        $validationData = Redis::get($cacheKey);

        if (!$validationData) {
            return null;
        }

        $validation = json_decode($validationData, true);
        if (!$validation) {
            return null;
        }

        $userId = $validation['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        // Para tokens de microservicio, obtener datos específicos
        if ($tokenType === 'microservice' || $tokenType === 'azure') {
            return $this->buildMicroserviceSession($validation, $userId);
        }

        // Para tokens locales, obtener datos del usuario
        return $this->buildLocalSession($validation, $userId);
    }

    /**
     * Construye sesión para tokens de microservicio
     */
    private function buildMicroserviceSession(array $validation, int $userId): array
    {
        $claims = $validation['claims'] ?? [];

        // Obtener datos del usuario desde cache o construir básicos
        $userCacheKey = "graph:user:{$userId}";
        $userData = Redis::get($userCacheKey);

        if ($userData) {
            $userData = json_decode($userData, true);
        } else {
            $userData = [
                'id' => $userId,
                'microservices_data' => []
            ];
        }

        return [
            'user_id' => $userId,
            'microservices_by_id' => $this->buildMicroservicesById($userData['microservices_data'] ?? []),
            'microservices_by_code' => $this->buildMicroservicesByCode($userData['microservices_data'] ?? []),
            'tenant_id' => $claims['tid'] ?? 'default',
            'token_type' => 'microservice'
        ];
    }

    /**
     * Construye sesión para tokens locales
     */
    private function buildLocalSession(array $validation, int $userId): array
    {
        // Obtener datos del usuario local desde cache
        $userCacheKey = "local:user:{$userId}";
        $userData = Redis::get($userCacheKey);

        if ($userData) {
            $userData = json_decode($userData, true);
            return [
                'user_id' => $userId,
                'microservices_by_id' => $userData['microservices_by_id'] ?? [],
                'microservices_by_code' => $userData['microservices_by_code'] ?? [],
                'token_type' => 'local'
            ];
        }

        return [
            'user_id' => $userId,
            'microservices_by_id' => [],
            'microservices_by_code' => [],
            'token_type' => 'local'
        ];
    }

    /**
     * Construye índice de microservicios por ID
     */
    private function buildMicroservicesById(array $microservicesData): array
    {
        $result = [];
        foreach ($microservicesData as $data) {
            if (isset($data['id'])) {
                $result[$data['id']] = $data;
            }
        }
        return $result;
    }

    /**
     * Construye índice de microservicios por código
     */
    private function buildMicroservicesByCode(array $microservicesData): array
    {
        $result = [];
        foreach ($microservicesData as $data) {
            if (isset($data['code'])) {
                $result[$data['code']] = $data;
            }
        }
        return $result;
    }

    /**
     * Encuentra la entrada del microservicio en los datos de sesión
     */
    private function findMicroserviceEntry(array $sessionData, ?string $microId, ?string $microCode): ?array
    {
        // Buscar por ID primero
        if ($microId && isset($sessionData['microservices_by_id'][$microId])) {
            return $sessionData['microservices_by_id'][$microId];
        }

        // Buscar por código si no se encontró por ID
        if ($microCode && isset($sessionData['microservices_by_code'][$microCode])) {
            return $sessionData['microservices_by_code'][$microCode];
        }

        return null;
    }

    /**
     * Establece atributos en la request para uso posterior
     */
    private function setRequestAttributes(Request $request, array $sessionData, array $microserviceEntry, ?string $microId): void
    {
        $request->attributes->set('user_id', $sessionData['user_id'] ?? null);
        $request->attributes->set('microservice_id', $microId ?? $microserviceEntry['id'] ?? null);
        $request->attributes->set('microservice_code', $microserviceEntry['code'] ?? null);
        $request->attributes->set('microservice_permissions', $microserviceEntry['permissions'] ?? []);
        $request->attributes->set('microservice_roles', $microserviceEntry['roles'] ?? []);
        $request->attributes->set('tenant_id', $sessionData['tenant_id'] ?? 'default');
    }

    /**
     * Obtiene la clave de cache según el tipo de token
     */
    private function getCacheKey(string $tokenType, string $tokenHash): string
    {
        return match ($tokenType) {
            'local' => "jwt:local:{$tokenHash}",
            'azure' => "jwt:validated:{$tokenHash}",
            'microservice' => "jwt:ms:{$tokenHash}",
            default => "jwt:validated:{$tokenHash}"
        };
    }
}
