<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Process
 */
class ProcessResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'processCategoryId' => $this->process_category_id,
            'parentId' => $this->parent_id,
            'name' => $this->name,
            'code' => $this->code,
            'order' => $this->order,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'processCategory' => $this->when(
                $this->relationLoaded('processCategory'),
                fn () => new ProcessCategoryResource($this->processCategory)
            ),
            'parent' => $this->when(
                $this->relationLoaded('parent'),
                fn () => new ProcessResource($this->parent)
            ),
            'children' => ProcessResource::collection($this->whenLoaded('children')),
            'requiredDocuments' => RequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'processCategoryId' => $this->process_category_id,
            'parentId' => $this->parent_id,
            'name' => $this->name,
            'code' => $this->code,
            'order' => $this->order,
        ];
    }

    protected function getResourceType(): string
    {
        return 'process';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($process) {
                return [
                    'value' => $process->id,
                    'label' => $process->name,
                    'code' => $process->code,
                ];
            }),
            'count' => count($collection)
        ];
    }
}
