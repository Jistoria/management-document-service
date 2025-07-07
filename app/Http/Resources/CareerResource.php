<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Resource for Career API responses
 */
class CareerResource extends BaseResource
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
            'departmentId' => $this->department_id,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,

            // Conditional relationships
            'department' => $this->when(
                $this->shouldInclude('department', $request),
                function () {
                    return $this->whenLoaded('department', function () {
                        return new DepartmentResource($this->department);
                    });
                }
            ),

            'headOffice' => $this->when(
                $this->shouldInclude('head_office', $request),
                function () {
                    return $this->whenLoaded('department.headOffice', function () {
                        return new HeadOfficeResource($this->department->headOffice);
                    });
                }
            ),
        ];
    }

    /**
     * Transform for dropdown/select usage
     */
    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($career) {
                return [
                    'value' => $career->id,
                    'label' => $career->name,
                    'code' => $career->code,
                    'department_id' => $career->department_id,
                    'department_name' => $career->department?->name,
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
            'subsystems' => $this->whenLoaded('subsystems', function () {
                return $this->subsystems->map(function ($subsystem) {
                    return [
                        'id' => $subsystem->id,
                        'name' => $subsystem->name,
                        'code' => $subsystem->code,
                    ];
                });
            }),
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                    'code' => $this->department->code,
                    'head_office' => $this->department->headOffice ? [
                        'id' => $this->department->headOffice->id,
                        'name' => $this->department->headOffice->name,
                        'code' => $this->department->headOffice->code,
                    ] : null,
                ];
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

    /**
     * Get minimal fields for listing views
     */
    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'career';
    }
}
