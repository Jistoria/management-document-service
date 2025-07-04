<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\HeadOffice\StoreHeadOfficeRequest;
use App\Http\Requests\HeadOffice\UpdateHeadOfficeRequest;
use App\Services\HeadOfficeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Head Office operations
 *
 * Handles HTTP requests for head office CRUD operations
 * using the HeadOfficeService and catchSync helper.
 */
class HeadOfficeController extends Controller
{
    protected HeadOfficeService $headOfficeService;

    public function __construct(HeadOfficeService $headOfficeService)
    {
        $this->headOfficeService = $headOfficeService;
    }

    /**
     * Display a listing of head offices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $perPage = $request->input('per_page', 15);
            $filters = [
                'search' => $request->input('search'),
                'code' => $request->input('code'),
                'created_by' => $request->input('created_by')
            ];

            if ($request->has('paginate') && $request->input('paginate') !== 'false') {
                return $this->headOfficeService->getPaginated($perPage, $filters);
            }

            return $this->headOfficeService->getAll();
        }, 'Sedes obtenidas exitosamente');
    }

    /**
     * Store a newly created head office
     *
     * @param StoreHeadOfficeRequest $request
     * @return JsonResponse
     */
    public function store(StoreHeadOfficeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $data = $request->validated();

            // Create head office
            $headOffice = $this->headOfficeService->create($data);

            return $headOffice->load(['departments']);
        }, 'Sede creada exitosamente', 201);
    }

    /**
     * Display the specified head office
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $headOffice = $this->headOfficeService->findById($id);

            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada');
            }

            return $headOffice;
        }, 'Sede obtenida exitosamente');
    }

    /**
     * Update the specified head office
     *
     * @param UpdateHeadOfficeRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateHeadOfficeRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $data = $request->validated();

            // Update head office
            $headOffice = $this->headOfficeService->update($id, $data);

            return $headOffice;
        }, 'Sede actualizada exitosamente');
    }

    /**
     * Remove the specified head office
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->headOfficeService->delete($id);

            return ['deleted' => true, 'id' => $id];
        }, 'Sede eliminada exitosamente');
    }

    /**
     * Restore the specified head office
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $headOffice = $this->headOfficeService->restore($id);

            return $headOffice;
        }, 'Sede restaurada exitosamente');
    }

    /**
     * Get head office hierarchy with all relationships
     *
     * @param string $id
     * @return JsonResponse
     */
    public function hierarchy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $hierarchy = $this->headOfficeService->getFullHierarchy($id);

            if (!$hierarchy) {
                throw new \InvalidArgumentException('Sede no encontrada');
            }

            return $hierarchy;
        }, 'Jerarquía obtenida exitosamente');
    }

    /**
     * Get head office statistics
     *
     * @param string $id
     * @return JsonResponse
     */
    public function statistics(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            return $this->headOfficeService->getStatistics($id);
        }, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * Find head office by code
     *
     * @param string $code
     * @return JsonResponse
     */
    public function findByCode(string $code): JsonResponse
    {
        return catchSync(function () use ($code) {
            $headOffice = $this->headOfficeService->findByCode($code);

            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada con el código especificado');
            }

            return $headOffice;
        }, 'Sede encontrada exitosamente');
    }

    /**
     * Bulk delete head offices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $ids = $request->input('ids', []);

            if (empty($ids) || !is_array($ids)) {
                throw new \InvalidArgumentException('Se requiere un array de IDs');
            }

            $deletedCount = $this->headOfficeService->bulkDelete($ids);

            return [
                'deleted_count' => $deletedCount,
                'total_requested' => count($ids)
            ];
        }, 'Eliminación masiva completada');
    }
}
