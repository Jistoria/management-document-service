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
            'processes' => ProcessResource::collection($this->whenLoaded('processes')),
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
}
