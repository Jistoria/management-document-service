<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for Head Office API responses
 *
 * Transforms HeadOffice model data into consistent API responses.
 */
class HeadOfficeResource extends JsonResource
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

            // Conditional relationships
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'code' => $department->code,
                        'created_at' => $department->created_at?->toISOString(),
                        'updated_at' => $department->updated_at?->toISOString(),
                    ];
                });
            }),
            'departments_count' => $this->when(
                $this->relationLoaded('departments'),
                fn() => $this->departments->count()
            ),

            // Additional computed fields
            'has_departments' => $this->when(
                $this->relationLoaded('departments'),
                fn() => $this->departments->count() > 0
            ),

            // Hierarchy information when loaded
            'hierarchy' => $this->when(
                $this->relationLoaded('departments.careers'),
                fn() => [
                    'departments_count' => $this->departments->count(),
                    'careers_count' => $this->departments->sum(function ($dept) {
                        return $dept->careers ? $dept->careers->count() : 0;
                    }),
                    'total_active_departments' => $this->activeDepartments()->count(),
                ]
            ),
        ];
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
            'meta' => [
                'resource_type' => 'head_office',
                'generated_at' => now()->toISOString(),
            ],
        ];
    }
}
