<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProcessCategoryResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'subsystemId' => $this->subsystem_id,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'processes' => ProcessResource::collection($this->whenLoaded('processesRoot') ?? $this->whenLoaded('processes')),
            'processesCount' => $this->when(
                $this->relationLoaded('processesRoot') || $this->relationLoaded('processes'),
                fn() => ($this->processesRoot?->count() ?? 0) + ($this->processes?->count() ?? 0)
            ),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    protected function getResourceType(): string
    {
        return 'processCategory';
    }

    /**
     * Format collection for dropdown usage
     */
    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($cat) {
                return [
                    'value' => $cat->id,
                    'label' => $cat->name,
                    'code' => $cat->code,
                ];
            }),
            'count' => count($collection)
        ];
    }

    /**
     * Format for simple reference
     */
    public static function asReference($category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
        ];
    }
}
