<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Resource for Head Office API responses
 *
 * Transforms HeadOffice model data into consistent API responses.
 * Supports multiple transformation contexts: pagination, collections, detailed, minimal.
 */
class HeadOfficeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,

            // Conditional relationships based on what was loaded by service
            'departments' => $this->when(
                $this->relationLoaded('departments') && $this->wasIncludeRequested('departments'),
                function () {
                    return DepartmentResource::collection($this->departments);
                }
            ),

            'departmentsCount' => $this->when(
                $this->relationLoaded('departments'),
                fn() => $this->departments->count()
            ),

            // Statistics when specifically requested
            'statistics' => $this->when(
                $this->relationLoaded('departments') && $this->wasIncludeRequested('statistics'),
                function () {
                    return [
                        'departmentsCount' => $this->departments->count(),
                        'careersCount' => $this->departments->sum(function ($dept) {
                            return $dept->relationLoaded('careers') ? $dept->careers->count() : 0;
                        }),
                        'hasDepartments' => $this->departments->isNotEmpty(),
                    ];
                }
            ),

            // Hierarchy information when specifically requested and fully loaded
            'hierarchy' => $this->when(
                $this->wasIncludeRequested('hierarchy')
                    && $this->relationLoaded('departments')
                    && $this->departments->every(fn($d) => $d->relationLoaded('careers'))
                    && $this->departments->every(fn($d) => $d->careers->every(fn($c) => $c->relationLoaded('subsystems'))),
                fn() => [
                    'departmentsCount' => $this->departments->count(),
                    'careersCount' => $this->departments->sum(function ($dept) {
                        return $dept->careers ? $dept->careers->count() : 0;
                    }),
                    'departments' => DepartmentResource::collection($this->departments)
                        ->map(function ($dept) {
                            return array_merge($dept->toArray(request()), [
                                'careers' => CareerResource::collection($dept->careers ?? collect())
                            ]);
                        }),
                ]
            ),

        ];
    }

    /**
     * Get minimal fields for listing views
     */
    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'departmentsCount' => $this->departments()->count(),
        ];
    }

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'headOffice';
    }

    /**
     * Transform for dropdown/select usage
     */
    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($headOffice) {
                return [
                    'value' => $headOffice->id,
                    'label' => $headOffice->name,
                    'code' => $headOffice->code,
                ];
            }),
            'count' => count($collection)
        ];
    }

    /**
     * Transform for hierarchy view
     */
    public function withHierarchy(): array
    {
        return array_merge($this->toArray(request()), [
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'code' => $department->code,
                        'careers' => $department->careers ? $department->careers->map(function ($career) {
                            return [
                                'id' => $career->id,
                                'name' => $career->name,
                                'code' => $career->code,
                            ];
                        }) : [],
                    ];
                });
            }),
        ]);
    }

    /**
     * Check if a specific include was requested by the service
     */
    protected function wasIncludeRequested(string $include): bool
    {
        $requestedIncludes = $this->resource->getAttribute('_requested_includes') ?? [];
        return in_array($include, $requestedIncludes);
    }

    /**
     * Additional metadata to include in the response.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => $this->getDetailedMeta(),
        ];
    }
}
