<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Resource for Department API responses
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
            'name' => $this->name,
            'code' => $this->code,
            'head_office_id' => $this->head_office_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'version' => $this->version,

            // Conditional relationships
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
}
