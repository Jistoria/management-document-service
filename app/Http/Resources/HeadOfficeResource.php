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
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'version' => $this->version,

            // Conditional relationships based on includes
            'departments' => $this->when(
                $this->shouldInclude('departments', $request),
                function () {
                    return $this->whenLoaded('departments', function () {
                        return DepartmentResource::collection($this->departments);
                    });
                }
            ),

            'departments_count' => $this->when(
                $this->relationLoaded('departments'),
                fn() => $this->departments->count()
            ),

            // Statistics when requested
            'statistics' => $this->when(
                $this->shouldInclude('statistics', $request),
                function () {
                    return [
                        'departments_count' => $this->departments()->count(),
                        'careers_count' => $this->departments()
                            ->withCount('careers')
                            ->get()
                            ->sum('careers_count'),
                        'has_departments' => $this->departments()->exists(),
                    ];
                }
            ),

            // Hierarchy information when loaded
            'hierarchy' => $this->when(
                $this->shouldInclude('hierarchy', $request) && $this->relationLoaded('departments.careers'),
                fn() => [
                    'departments_count' => $this->departments->count(),
                    'careers_count' => $this->departments->sum(function ($dept) {
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
            'departments_count' => $this->departments()->count(),
        ];
    }

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'head_office';
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
