<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Closure;

class ApiIndexBuilder
{
    /**
     * Estrategias de respuesta como closures key-value
     * @var array<string, Closure>
     */
    protected static array $strategies = [];

    /**
     * Registrar (o reemplazar) estrategias externas
     */
    public static function setStrategies(array $strategies): void
    {
        self::$strategies = $strategies;
    }

    /**
     * Agregar una estrategia (útil para extensiones)
     */
    public static function addStrategy(string $key, Closure $handler): void
    {
        self::$strategies[$key] = $handler;
    }

    /**
     * Constructor estático para inicializar estrategias por defecto
     */
    protected static function bootDefaultStrategies(): void
    {
        if (!empty(self::$strategies)) {
            return; // ya inicializado
        }

        self::$strategies = [
            // Paginación
            'paginate' => fn($ctx) => $ctx['resource']::paginated(
                $ctx['service']->getPaginated(
                    $ctx['perPage'],
                    $ctx['filters']
                )
            ),

            // Vista mínima con campos básicos
            'minimal' => function ($ctx) {
                $collection = $ctx['service']->getAll($ctx['filters']);
                return [
                    'data' => $collection->map(fn($model) => (new $ctx['resource']($model))->minimal()),
                    'count' => $collection->count(),
                ];
            },

            // Formato dropdown para selects
            'dropdown' => fn($ctx) => $ctx['resource']::forDropdown(
                $ctx['service']->getAll($ctx['filters'])
            ),

            // Formato pluck personalizado
            'pluck' => fn($ctx) => $ctx['resource']::pluck(
                $ctx['service']->getAll($ctx['filters']),
                $ctx['request']->input('pluckKey') ?? $ctx['request']->input('pluck_key', 'id'),
                $ctx['request']->input('pluckLabel') ?? $ctx['request']->input('pluck_label', 'name')
            ),

            // Colección simple (fallback por defecto)
            'collection' => fn($ctx) => $ctx['resource']::simpleCollection(
                $ctx['service']->getAll($ctx['filters'])
            ),
        ];
    }

    /**
     * Construye la respuesta API usando estrategias
     *
     * @param object $service - Instancia del servicio
     * @param string $resource - Clase del resource
     * @param Request $request - Request HTTP
     * @param array $filters - Filtros adicionales
     * @param int $defaultPerPage - Paginación por defecto
     * @return mixed
     */
    public static function build(
        object $service,
        string $resource,
        Request $request,
        array $filters = [],
        int $defaultPerPage = 15
    ) {
        self::bootDefaultStrategies();

        // Determinar el formato solicitado
        $format = self::determineFormat($request);

        // Validar que la estrategia existe
        if (!isset(self::$strategies[$format])) {
            throw new \InvalidArgumentException("Formato no soportado: {$format}");
        }

        // Contexto compartido para las estrategias
        $context = [
            'service' => $service,
            'resource' => $resource,
            'request' => $request,
            'filters' => $filters,
            'perPage' => (int) ($request->input('perPage') ?? $request->input('per_page', $defaultPerPage)),
            'format' => $format,
        ];

        // Ejecutar la estrategia correspondiente
        return self::$strategies[$format]($context);
    }

    /**
     * Determina el formato basado en los parámetros del request
     */
    protected static function determineFormat(Request $request): string
    {
        // 1. Prioridad al parámetro 'format' explícito
        $explicitFormat = $request->input('format');
        if ($explicitFormat && isset(self::$strategies[$explicitFormat])) {
            return $explicitFormat;
        }

        // 2. Checks de compatibilidad con parámetros legacy y camelCase
        $formatChecks = [
            'pluck' => fn($req) => $req->filled('pluckKey') || $req->filled('pluck_key') || $req->filled('pluck'),
            'minimal' => fn($req) => $req->boolean('minimal'),
            'paginate' => fn($req) => $req->boolean('paginate', false),
        ];

        foreach ($formatChecks as $format => $check) {
            if ($check($request)) {
                return $format;
            }
        }

        // 3. Fallback por defecto
        return 'collection';
    }

    /**
     * Extrae filtros del request de forma inteligente
     */
    public static function extractFilters(Request $request, array $allowedFilters = []): array
    {
        $filters = [];

        // Filtros comunes que la mayoría de entidades soportan
        $commonFilters = ['search', 'code', 'createdBy', 'created_by'];

        // Combinar filtros comunes con específicos de la entidad
        $allFilters = array_merge($commonFilters, $allowedFilters);

        foreach ($allFilters as $filter) {
            if ($request->filled($filter)) {
                // Convertir camelCase a snake_case para compatibilidad interna
                $internalFilter = $filter === 'createdBy' ? 'created_by' : $filter;
                $filters[$internalFilter] = $request->input($filter);
            }
        }

        // Filtros específicos con conversión camelCase
        $camelCaseFilters = [
            'headOfficeId' => 'head_office_id',
            'departmentId' => 'department_id',
            'processId' => 'process_id',
            'documentTypeId' => 'document_type_id',
            'academicRoleId' => 'academic_role_id',
            'metadataSchemaId' => 'metadata_schema_id',
            'isPublic' => 'is_public',
            'externalUserId' => 'external_user_id',
            'externalOrganizationId' => 'external_organization_id',
            'storageUnitTypeId' => 'storage_unit_type_id',
            'parentId' => 'parent_id',
        ];

        foreach ($camelCaseFilters as $camelCase => $snakeCase) {
            if ($request->filled($camelCase)) {
                $filters[$snakeCase] = $request->input($camelCase);
            } elseif ($request->filled($snakeCase)) {
                $filters[$snakeCase] = $request->input($snakeCase);
            }
        }

        return $filters;
    }

    /**
     * Extrae includes del request
     */
    public static function extractIncludes(Request $request): array
    {
        $includes = $request->get('include', '');

        if (empty($includes)) {
            return [];
        }

        return array_map('trim', explode(',', $includes));
    }

    /**
     * Registra una estrategia personalizada para un proyecto específico
     */
    public static function registerCustomStrategy(string $format, Closure $strategy): void
    {
        self::bootDefaultStrategies();
        self::$strategies[$format] = $strategy;
    }

    /**
     * Ejemplos de estrategias personalizadas que se pueden registrar
     */
    public static function registerExampleStrategies(): void
    {
        // Estrategia para exportar a CSV
        self::registerCustomStrategy('csv', function ($ctx) {
            $collection = $ctx['service']->getAll($ctx['filters']);
            $data = $collection->map(fn($model) => (new $ctx['resource']($model))->toArray($ctx['request']));

            return [
                'type' => 'csv',
                'data' => $data,
                'headers' => $data->isNotEmpty() ? array_keys($data->first()) : [],
                'count' => $data->count(),
            ];
        });

        // Estrategia para estadísticas básicas
        self::registerCustomStrategy('stats', function ($ctx) {
            $collection = $ctx['service']->getAll($ctx['filters']);

            return [
                'type' => 'statistics',
                'total_count' => $collection->count(),
                'created_today' => $collection->filter(
                    fn($item) =>
                    $item->created_at?->isToday()
                )->count(),
                'created_this_week' => $collection->filter(
                    fn($item) =>
                    $item->created_at?->isCurrentWeek()
                )->count(),
                'created_this_month' => $collection->filter(
                    fn($item) =>
                    $item->created_at?->isCurrentMonth()
                )->count(),
            ];
        });

        // Estrategia para árbol jerárquico
        self::registerCustomStrategy('tree', function ($ctx) {
            $collection = $ctx['service']->getAll($ctx['filters']);

            return [
                'type' => 'tree',
                'data' => $collection->map(fn($model) => (new $ctx['resource']($model))->withHierarchy()),
                'count' => $collection->count(),
            ];
        });
    }

    /**
     * Reset todas las estrategias (útil para testing)
     */
    public static function resetStrategies(): void
    {
        self::$strategies = [];
    }

    /**
     * Verificar si una estrategia existe
     */
    public static function hasStrategy(string $format): bool
    {
        self::bootDefaultStrategies();
        return isset(self::$strategies[$format]);
    }

    /**
     * Obtiene todas las estrategias disponibles
     */
    public static function getAvailableFormats(): array
    {
        self::bootDefaultStrategies();
        return array_keys(self::$strategies);
    }
}
