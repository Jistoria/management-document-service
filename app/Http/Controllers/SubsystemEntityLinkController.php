<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatus;
use App\Helpers\SubsystemEntityMap;
use App\Http\Requests\Subsystem\DeleteEntityLinkRequest;
use App\Http\Requests\Subsystem\StoreEntityLinkRequest;
use App\Services\SubsystemEntityLinkService;
use App\Helpers\ApiResponse;
use App\Http\Resources\SubsystemResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use function App\Helpers\catchSync;

/**
 * @OA\Tag(
 *     name="Subsystem Entity Links",
 *     description="API endpoints for managing polymorphic relationships between subsystems and entities"
 * )
 */
class SubsystemEntityLinkController extends Controller
{
    protected SubsystemEntityLinkService $linkService;

    public function __construct(SubsystemEntityLinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    /**
     * @OA\Get(
     *     path="/subsystem-entity-links",
     *     summary="Get subsystems linked to a specific entity",
     *     tags={"Subsystem Entity Links"},
     *     @OA\Parameter(
     *         name="entity_type",
     *         in="query",
     *         required=true,
     *         description="Type of entity",
     *         @OA\Schema(
     *             type="string",
     *             enum={"head_office", "department", "career"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="entity_id",
     *         in="query",
     *         required=true,
     *         description="UUID of the entity",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of subsystems linked to the entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subsystems retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entity not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Entity not found")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $validated = $request->validate([
                'entity_type' => 'required|string|in:head_office,department,career',
                'entity_id' => 'required|uuid'
            ]);

            $subsystems = $this->linkService->getLinksForEntity(
                $validated['entity_type'],
                $validated['entity_id']
            );

            return SubsystemResource::collection($subsystems);
        }, status: HttpStatus::OK);
    }

    /**
     * @OA\Post(
     *     path="/subsystem-entity-links",
     *     summary="Attach a subsystem to an entity",
     *     tags={"Subsystem Entity Links"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"subsystem_id", "entity_type", "entity_id"},
     *             @OA\Property(property="subsystem_id", type="string", format="uuid", description="UUID of the subsystem"),
     *             @OA\Property(
     *                 property="entity_type",
     *                 type="string",
     *                 enum={"head_office", "department", "career"},
     *                 description="Type of entity"
     *             ),
     *             @OA\Property(property="entity_id", type="string", format="uuid", description="UUID of the entity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subsystem attached successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subsystem attached successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Relationship already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Relationship already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreEntityLinkRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                $validated = $request->validated();
                $attached = $this->linkService->attachSubsystemToEntity(
                    $validated['subsystem_id'],
                    $validated['entity_type'],
                    $validated['entity_id']
                );
            },
            status: HttpStatus::CREATED
        );
    }

    /**
     * @OA\Delete(
     *     path="/subsystem-entity-links",
     *     summary="Detach a subsystem from an entity",
     *     tags={"Subsystem Entity Links"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"subsystem_id", "entity_type", "entity_id"},
     *             @OA\Property(property="subsystem_id", type="string", format="uuid", description="UUID of the subsystem"),
     *             @OA\Property(
     *                 property="entity_type",
     *                 type="string",
     *                 enum={"head_office", "department", "career"},
     *                 description="Type of entity"
     *             ),
     *             @OA\Property(property="entity_id", type="string", format="uuid", description="UUID of the entity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subsystem detached successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subsystem detached successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relationship not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Relationship not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function destroy(DeleteEntityLinkRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                $validated = $request->validated();
                $attached = $this->linkService->detachSubsystemFromEntity(
                    $validated['subsystem_id'],
                    $validated['entity_type'],
                    $validated['entity_id']
                );
            },
            status: HttpStatus::NO_CONTENT
        );
    }
}
