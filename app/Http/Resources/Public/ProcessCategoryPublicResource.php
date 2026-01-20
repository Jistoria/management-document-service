<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Public-safe resource for Process Category
 * 
 * Expone únicamente datos seguros sin información sensible
 */
class ProcessCategoryPublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            
            // Conteo de procesos
            'processesCount' => $this->when(
                $this->relationLoaded('processes'),
                fn() => $this->processes->count()
            ),
        ];
    }

    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }

    protected function getResourceType(): string
    {
        return 'publicProcessCategory';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->name,
                'code' => $item->code,
            ])->values(),
            'count' => $collection->count()
        ];
    }

    public function with(Request $request): array
    {
        return [];
    }
}
