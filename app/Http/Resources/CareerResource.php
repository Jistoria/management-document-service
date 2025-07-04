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
            'department_id' => $this->department_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
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

            'head_office' => $this->when(
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
