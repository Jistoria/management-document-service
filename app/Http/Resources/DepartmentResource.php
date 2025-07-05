<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Resource for Department API responses
 *
 * Transforms Department model data into consistent API responses.
 * Supports multiple transformation contexts: pagination, collections, detailed, minimal.
 */
class DepartmentResource extends BaseResource
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
            'head_office_id' => $this->head_office_id,
            'name' => $this->name,
            'code' => $this->code,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'version' => $this->version,

            // Conditional relationships based on includes
            'head_office' => $this->when(
                $this->shouldInclude('head_office', $request),
                function () {
                    return $this->whenLoaded('headOffice', function () {
                        return new HeadOfficeResource($this->headOffice);
                    });
                }
            ),

            'careers' => $this->when(
                $this->shouldInclude('careers', $request),
                function () {
                    return $this->whenLoaded('careers', function () {
                        return CareerResource::collection($this->careers);
                    });
                }
            ),

            'careers_count' => $this->when(
                $this->relationLoaded('careers'),
                fn() => $this->careers->count()
            ),

            // Statistics when requested
            'statistics' => $this->when(
                $this->shouldInclude('statistics', $request),
                function () {
                    return [
                        'careers_count' => $this->careers()->count(),
                        'has_careers' => $this->careers()->exists(),
                        'head_office_name' => $this->headOffice?->name,
                    ];
                }
            ),

            // Hierarchy information when loaded
            'hierarchy' => $this->when(
                $this->shouldInclude('hierarchy', $request) && $this->relationLoaded('careers.subsystems'),
                fn() => [
                    'head_office' => [
                        'id' => $this->headOffice?->id,
                        'name' => $this->headOffice?->name,
                        'code' => $this->headOffice?->code,
                    ],
                    'careers_count' => $this->careers->count(),
                    'careers' => CareerResource::collection($this->careers ?? collect())
                        ->map(function ($career) {
                            return array_merge($career->toArray(request()), [
                                'subsystems' => $career->subsystems ?? collect()
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
            'head_office_id' => $this->head_office_id,
            'careers_count' => $this->careers()->count(),
        ];
    }

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'department';
    }

    /**
     * Transform for dropdown/select usage
     */
    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($department) {
                return [
                    'value' => $department->id,
                    'label' => $department->name,
                    'code' => $department->code,
                    'head_office_id' => $department->head_office_id,
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
            'careers' => $this->whenLoaded('careers', function () {
                return $this->careers->map(function ($career) {
                    return [
                        'id' => $career->id,
                        'name' => $career->name,
                        'code' => $career->code,
                        'subsystems' => $career->subsystems ? $career->subsystems->map(function ($subsystem) {
                            return [
                                'id' => $subsystem->id,
                                'name' => $subsystem->name,
                                'code' => $subsystem->code,
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
