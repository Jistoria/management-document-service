<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class StorageUnitResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storageUnitTypeId' => $this->storage_unit_type_id,
            'parentId' => $this->parent_id,
            'label' => $this->label,
            'code' => $this->code,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,

            'storageUnitType' => $this->when(
                $this->relationLoaded('storageUnitType'),
                fn () => new StorageUnitTypeResource($this->storageUnitType)
            ),
            'parent' => $this->when(
                $this->relationLoaded('parent'),
                fn () => new StorageUnitResource($this->parent)
            ),
            'children' => StorageUnitResource::collection($this->whenLoaded('children')),
            'childrenCount' => $this->when(
                $this->relationLoaded('children'),
                fn () => $this->children->count()
            ),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'code' => $this->code,
            'storageUnitTypeId' => $this->storage_unit_type_id,
            'parentId' => $this->parent_id,
        ];
    }

    protected function getResourceType(): string
    {
        return 'storage_unit';
    }

    public static function forDropdown($collection): array
    {
        return parent::dropdown($collection, 'id', 'label');
    }
}
