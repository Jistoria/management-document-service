<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\PublicFiltersRequest;
use App\Http\Resources\Public\HeadOfficePublicResource;
use App\Http\Resources\Public\DepartmentPublicResource;
use App\Http\Resources\Public\CareerPublicResource;
use App\Http\Resources\Public\ProcessCategoryPublicResource;
use App\Http\Resources\Public\ProcessPublicResource;
use App\Http\Resources\Public\DocumentTypePublicResource;
use App\Services\HeadOfficeService;
use App\Services\DepartmentService;
use App\Services\CareerService;
use App\Services\ProcessCategoryService;
use App\Services\ProcessService;
use App\Services\DocumentTypeService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use function App\Helpers\catchSync;

/**
 * Controlador para catálogos públicos
 * 
 * Endpoints sin autenticación para uso en frontend público
 * Solo expone datos seguros (id, code, name) sin información sensible
 * 
 * @OA\Tag(
 *     name="Public API",
 *     description="Endpoints públicos sin autenticación. Rate limited a 60 req/min por IP. Solo expone datos seguros sin información sensible (created_by, updated_by, version)."
 * )
 */
class PublicCatalogController extends Controller
{
    public function __construct(
        protected HeadOfficeService $headOfficeService,
        protected DepartmentService $departmentService,
        protected CareerService $careerService,
        protected ProcessCategoryService $processCategoryService,
        protected ProcessService $processService,
        protected DocumentTypeService $documentTypeService
    ) {}

    /**
     * @OA\Get(
     *     path="/public/head-offices",
     *     operationId="getPublicHeadOffices",
     *     tags={"Public API"},
     *     summary="Obtener sedes (público)",
     *     description="Listado de sedes activas sin autenticación. Solo expone datos seguros (id, code, name). Soporta múltiples formatos de respuesta.",
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Formato de respuesta",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"collection", "minimal", "dropdown", "pluck", "paginate"},
     *             default="collection"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100, example="central")
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Filtrar por código específico",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=20, example="CENTRAL")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Elementos por página (máximo 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página (solo con format=paginate)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sedes obtenidas exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/PublicSuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         oneOf={
     *                             @OA\Schema(ref="#/components/schemas/PublicCollectionResponse"),
     *                             @OA\Schema(ref="#/components/schemas/PublicDropdownResponse"),
     *                             @OA\Schema(ref="#/components/schemas/PublicMinimalResponse")
     *                         }
     *                     )
     *                 )
     *             },
     *             example={
     *                 "success": true,
     *                 "message": "Sedes obtenidas exitosamente",
     *                 "data": {
     *                     "options": {
     *                         {"value": "0197d795-7572-7331-903b-3aeed9fb34c2", "label": "Sede Central", "code": "CENTRAL"},
     *                         {"value": "0197d795-7572-7331-903b-3aeed9fb34c3", "label": "Sede Norte", "code": "NORTE"}
     *                     },
     *                     "count": 2
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Demasiadas peticiones (Rate Limit excedido)",
     *         @OA\JsonContent(ref="#/components/schemas/RateLimitError")
     *     )
     * )
     */
    public function headOffices(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            return ApiIndexBuilder::build(
                service: $this->headOfficeService,
                resource: HeadOfficePublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Sedes obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/head-offices/{id}",
     *     operationId="getPublicHeadOffice",
     *     tags={"Public API"},
     *     summary="Detalle de sede (público)",
     *     description="Obtiene una sede específica por ID sin autenticación",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único de la sede (UUID)",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="0197d795-7572-7331-903b-3aeed9fb34c2"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/PublicSuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/PublicHeadOffice"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sede no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate Limit excedido",
     *         @OA\JsonContent(ref="#/components/schemas/RateLimitError")
     *     )
     * )
     */
    public function showHeadOffice(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $headOffice = $this->headOfficeService->findById($id);
            
            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada');
            }
            
            return new HeadOfficePublicResource($headOffice);
        }, 'Sede obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/departments",
     *     operationId="getPublicDepartments",
     *     tags={"Public API"},
     *     summary="Obtener departamentos (público)",
     *     description="Listado de departamentos activos sin autenticación. Puede filtrarse por sede.",
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         @OA\Schema(type="string", enum={"collection", "minimal", "dropdown", "pluck"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="head_office_id",
     *         in="query",
     *         description="Filtrar por sede específica",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         @OA\Schema(type="integer", minimum=1, maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamentos obtenidos exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(
     *         response=429,
     *         description="Rate Limit",
     *         @OA\JsonContent(ref="#/components/schemas/RateLimitError")
     *     )
     * )
     */
    public function departments(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            return ApiIndexBuilder::build(
                service: $this->departmentService,
                resource: DepartmentPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Departamentos obtenidos exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/departments/{id}",
     *     operationId="getPublicDepartment",
     *     tags={"Public API"},
     *     summary="Detalle de departamento (público)",
     *     description="Obtiene un departamento específico por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=404, description="Departamento no encontrado"),
     *     @OA\Response(response=429, description="Rate Limit", @OA\JsonContent(ref="#/components/schemas/RateLimitError"))
     * )
     */
    public function showDepartment(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $department = $this->departmentService->findById($id);
            
            if (!$department) {
                throw new \InvalidArgumentException('Departamento no encontrado');
            }
            
            return new DepartmentPublicResource($department);
        }, 'Departamento obtenido exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/careers",
     *     operationId="getPublicCareers",
     *     tags={"Public API"},
     *     summary="Obtener carreras (público)",
     *     description="Listado de carreras activas con posibilidad de filtrar por sede o departamento",
     *     @OA\Parameter(name="format", in="query", @OA\Schema(type="string", enum={"collection", "minimal", "dropdown", "pluck"})),
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", @OA\Schema(type="string", example="ingenieria")),
     *     @OA\Parameter(name="head_office_id", in="query", description="Filtrar por sede", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="department_id", in="query", description="Filtrar por departamento", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Carreras obtenidas exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=429, description="Rate Limit", @OA\JsonContent(ref="#/components/schemas/RateLimitError"))
     * )
     */
    public function careers(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            return ApiIndexBuilder::build(
                service: $this->careerService,
                resource: CareerPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Carreras obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/careers/{id}",
     *     operationId="getPublicCareer",
     *     tags={"Public API"},
     *     summary="Detalle de carrera (público)",
     *     description="Obtiene una carrera específica por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera obtenida exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=404, description="Carrera no encontrada"),
     *     @OA\Response(response=429, description="Rate Limit")
     * )
     */
    public function showCareer(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $career = $this->careerService->findById($id);
            
            if (!$career) {
                throw new \InvalidArgumentException('Carrera no encontrada');
            }
            
            return new CareerPublicResource($career);
        }, 'Carrera obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/departments/{id}/careers",
     *     operationId="getPublicCareersByDepartment",
     *     tags={"Public API"},
     *     summary="Carreras por departamento (público)",
     *     description="Obtiene todas las carreras de un departamento específico. Útil para crear filtros dependientes en formularios públicos.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del departamento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         @OA\Schema(type="string", enum={"collection", "minimal", "dropdown"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carreras del departamento obtenidas exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=429, description="Rate Limit")
     * )
     */
    public function careersByDepartment(PublicFiltersRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $filters = array_merge(
                $request->getPublicFilters(),
                ['department_id' => $id]
            );
            
            return ApiIndexBuilder::build(
                service: $this->careerService,
                resource: CareerPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Carreras obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/process-categories",
     *     operationId="getPublicProcessCategories",
     *     tags={"Public API"},
     *     summary="Obtener categorías de procesos (público)",
     *     description="Listado de categorías de procesos activas sin autenticación",
     *     @OA\Parameter(name="format", in="query", @OA\Schema(type="string", enum={"collection", "minimal", "dropdown"})),
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Categorías obtenidas exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=429, description="Rate Limit")
     * )
     */
    public function processCategories(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            return ApiIndexBuilder::build(
                service: $this->processCategoryService,
                resource: ProcessCategoryPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Categorías de procesos obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/document-types",
     *     operationId="getPublicDocumentTypes",
     *     tags={"Public API"},
     *     summary="Obtener tipos de documentos (público)",
     *     description="Listado de tipos de documentos activos sin autenticación",
     *     @OA\Parameter(name="format", in="query", @OA\Schema(type="string", enum={"collection", "minimal", "dropdown"})),
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", @OA\Schema(type="string", example="certificado")),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Tipos de documentos obtenidos exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=429, description="Rate Limit")
     * )
     */
    public function documentTypes(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            return ApiIndexBuilder::build(
                service: $this->documentTypeService,
                resource: DocumentTypePublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Tipos de documentos obtenidos exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/processes",
     *     operationId="getPublicProcesses",
     *     tags={"Public API"},
     *     summary="Obtener procesos (público)",
     *     description="Listado de procesos activos sin autenticación. Puede filtrarse por categoría de proceso.",
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Formato de respuesta",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"collection", "minimal", "dropdown", "pluck", "paginate"},
     *             default="collection"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100)
     *     ),
     *     @OA\Parameter(
     *         name="process_category_id",
     *         in="query",
     *         description="Filtrar por categoría de proceso",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="Filtrar por proceso padre (null para procesos raíz)",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid", nullable=true)
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Elementos por página (máximo 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Procesos obtenidos exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=429, description="Rate Limit", @OA\JsonContent(ref="#/components/schemas/RateLimitError"))
     * )
     */
    public function processes(PublicFiltersRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getPublicFilters();
            
            // Agregar filtros específicos de procesos
            if ($request->has('process_category_id')) {
                $filters['processCategoryId'] = $request->input('process_category_id');
            }
            if ($request->has('parent_id')) {
                $filters['parentId'] = $request->input('parent_id');
            }
            
            return ApiIndexBuilder::build(
                service: $this->processService,
                resource: ProcessPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Procesos obtenidos exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/processes/{id}",
     *     operationId="getPublicProcess",
     *     tags={"Public API"},
     *     summary="Detalle de proceso (público)",
     *     description="Obtiene un proceso específico por ID sin autenticación",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del proceso (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proceso obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=404, description="Proceso no encontrado"),
     *     @OA\Response(response=429, description="Rate Limit", @OA\JsonContent(ref="#/components/schemas/RateLimitError"))
     * )
     */
    public function showProcess(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $process = $this->processService->findById($id);
            
            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proceso no encontrado'
                ], 404);
            }
            
            return new ProcessPublicResource($process);
        }, 'Proceso obtenido exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/public/process-categories/{id}/processes",
     *     operationId="getPublicProcessesByCategory",
     *     tags={"Public API"},
     *     summary="Procesos por categoría (público)",
     *     description="Obtiene todos los procesos de una categoría específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría de proceso",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(name="format", in="query", @OA\Schema(type="string", enum={"collection", "minimal", "dropdown", "pluck"})),
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Procesos obtenidos exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/PublicSuccessResponse")
     *     ),
     *     @OA\Response(response=404, description="Categoría no encontrada"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=429, description="Rate Limit")
     * )
     */
    public function processesByCategory(PublicFiltersRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            // Verificar que la categoría existe
            $category = $this->processCategoryService->findById($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría de proceso no encontrada'
                ], 404);
            }
            
            $filters = $request->getPublicFilters();
            $filters['processCategoryId'] = $id;
            
            return ApiIndexBuilder::build(
                service: $this->processService,
                resource: ProcessPublicResource::class,
                request: $request,
                filters: $filters,
                defaultPerPage: 20
            );
        }, 'Procesos obtenidos exitosamente');
    }
}
