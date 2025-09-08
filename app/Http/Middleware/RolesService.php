<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RolesService
{
    public function __construct(private string $requiredPermission = 'document.view') {}

    public function handle(Request $request, Closure $next, $requiredPermission = null)
    {
        $this->requiredPermission = $requiredPermission ?? $this->requiredPermission;

        $tokenHash = $request->attributes->get('token_hash');
        $tokenValidation = $request->attributes->get('token_validation');

        if (!$tokenHash || !$tokenValidation) {
            return response()->json(['message' => 'Datos de autenticación faltantes'], 401);
        }

        // Obtener ID o CODE del microservicio desde headers
        $microCode = 'management-document-service'; // Usar nombre de la app como código

        // Obtener sesión unificada desde Redis
        $sessionData = $this->getSession($tokenHash);
        if (!$sessionData) {
            return response()->json(['message' => 'Sesión no encontrada'], 403);
        }

        Log::info('[RolesService] Session data retrieved', [
            'user_id' => $sessionData['user_id'] ?? 'unknown',
            'microservice_code' => $microCode
        ]);


        // Localizar datos del microservicio en la sesión
        $microserviceEntry = $this->findMicroserviceEntry($sessionData, null, $microCode);

        Log::info('[RolesService] Microservice lookup', [
            'microCode' => $microCode,
            'found_entry' => $microserviceEntry ? 'yes' : 'no',
            'available_codes' => array_keys($sessionData['microservices_by_code'] ?? []),
            'session_structure' => array_keys($sessionData)
        ]);

        if (!$microserviceEntry) {
            return response()->json(['message' => 'No permission'], 403);
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
        $this->setRequestAttributes($request, $sessionData, $microserviceEntry, $microCode);

        Log::info('[RolesService] Authorization successful', [
            'user_id' => $sessionData['user_id'],
            'permission' => $this->requiredPermission,
            'microservice' => $microserviceEntry['code'] ?? $microCode
        ]);

        return $next($request);
    }

    /**
     * Obtiene la sesión unificada desde Redis
     */
    private function getSession(string $tokenHash): ?array
    {
        // Primero intentar obtener sesión específica del microservicio
        $sessionKey = "laravel_database_session:{$tokenHash}";
        $sessionData = Redis::connection('default')->get($sessionKey);

        if ($sessionData) {
            return json_decode($sessionData, true) ?: null;
        }

        // Si no existe sesión específica, construir desde datos de validación
        return $this->buildSessionFromValidation($tokenHash);
    }

    /**
     * Construye datos de sesión desde la validación del token
     */
    private function buildSessionFromValidation(string $tokenHash): ?array
    {
        $cacheKey = $this->getCacheKey($tokenHash);
        $validationData = Redis::connection('default')->get($cacheKey);

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

        return $this->buildSession($validation, $userId);
    }

    /**
     * Construye sesión para tokens locales
     */
    private function buildSession(array $validation, string $userId): array
    {
        // Obtener datos del usuario local desde cache
        $userCacheKey = "laravel_database_user:{$userId}";
        $userData = Redis::connection('default')->get($userCacheKey);

        if ($userData) {
            $userData = json_decode($userData, true);

            // Si el usuario tiene datos de microservicios, usarlos
            if (isset($userData['microservices_by_id']) || isset($userData['microservices_by_code'])) {
                return [
                    'user_id' => $userId,
                    'microservices_by_id' => $userData['microservices_by_id'] ?? [],
                    'microservices_by_code' => $userData['microservices_by_code'] ?? [],
                    'token_type' => 'local'
                ];
            }

            // Si no tiene datos de microservicios pero tiene permisos, construir estructura básica
            if (isset($userData['permissions'])) {
                $appName = config('app.name');
                $microserviceData = [
                    'id' => $appName,
                    'code' => $appName,
                    'name' => $appName,
                    'permissions' => $userData['permissions'],
                    'roles' => $userData['roles'] ?? []
                ];

                return [
                    'user_id' => $userId,
                    'microservices_by_id' => [$appName => $microserviceData],
                    'microservices_by_code' => [$appName => $microserviceData],
                    'token_type' => 'local'
                ];
            }
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
        Log::info('[RolesService] Session data retrieved', ['session_data' => $sessionData, 'micro_id' => $microId, 'micro_code' => $microCode]);
        // Buscar por ID primero
        if ($microId && isset($sessionData['microservices_data']['by_id'][$microId])) {
            return $sessionData['by_id'][$microId];
        }

        // Buscar por código si no se encontró por ID
        if ($microCode && isset($sessionData['microservices_data']['by_code'][$microCode])) {
            return $sessionData['microservices_data']['by_code'][$microCode];
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
    private function getCacheKey(string $tokenHash): string
    {
        return "laravel_database_token:{$tokenHash}";
    }
}
