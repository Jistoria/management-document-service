<?php

namespace App\Http\Controllers;

use App\Models\SubsystemGroup;
use App\Helpers\ApiResponse;
use App\Services\SubsystemGroupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use function App\Helpers\catchSync;

/**
 * @OA\Tag(
 *     name="Subsystem Groups",
 *     description="API endpoints for managing subsystem groups"
 * )
 */
class SubsystemGroupController extends Controller
{
    public function __construct(
        protected SubsystemGroupService $subsystemGroupService,
    )
    {}

    /**
     * @OA\Get(
     *     path="/subsystem-groups",
     *     summary="Get all subsystem groups",
     *     tags={"Subsystem Groups"},
     *     @OA\Parameter(
     *         name="is_public",
     *         in="query",
     *         required=false,
     *         description="Filter by public/private groups",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of subsystem groups",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Groups retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="is_public", type="boolean")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SubsystemGroup::query();

            if ($request->has('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            $groups = $query->with(['subsystems:id,name,code'])->get();

            return ApiResponse::success($groups, 'Groups retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve groups: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/subsystem-groups",
     *     summary="Create a new subsystem group",
     *     tags={"Subsystem Groups"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", description="Group name"),
     *             @OA\Property(property="code", type="string", description="Unique group code"),
     *             @OA\Property(property="description", type="string", description="Group description"),
     *             @OA\Property(property="is_public", type="boolean", description="Whether group is public", default=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Group created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:subsystem_groups,code',
                'description' => 'nullable|string',
                'is_public' => 'boolean',
            ]);

            $group = SubsystemGroup::create($validated);

            return ApiResponse::success($group, 'Group created successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create group: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/subsystem-groups/{id}",
     *     summary="Get a specific subsystem group",
     *     tags={"Subsystem Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Group details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function show(SubsystemGroup $subsystemGroup): JsonResponse
    {
        try {
            $subsystemGroup->load(['subsystems:id,name,code']);
            return ApiResponse::success($subsystemGroup, 'Group retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve group: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/subsystem-groups/{id}",
     *     summary="Update a subsystem group",
     *     tags={"Subsystem Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_public", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Group updated successfully"
     *     )
     * )
     */
    public function update(Request $request, SubsystemGroup $subsystemGroup): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:100|unique:subsystem_groups,code,' . $subsystemGroup->id,
                'description' => 'nullable|string',
                'is_public' => 'boolean',
            ]);

            $subsystemGroup->update($validated);

            return ApiResponse::success($subsystemGroup, 'Group updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update group: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/subsystem-groups/{id}",
     *     summary="Delete a subsystem group",
     *     tags={"Subsystem Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Group deleted successfully"
     *     )
     * )
     */
    public function destroy(SubsystemGroup $subsystemGroup): JsonResponse
    {
        try {
            $subsystemGroup->delete();
            return ApiResponse::success(null, 'Group deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete group: ' . $e->getMessage());
        }
    }

    public function syncSubsystems(Request $request, SubsystemGroup $subsystemGroup): JsonResponse
    {
        return catchSync(
            function () use ($request, $subsystemGroup) {
                $this->subsystemGroupService->syncSubsystems($subsystemGroup, $request->get('subsystemsId', []));
            }
    );
    }
}
