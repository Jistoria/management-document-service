<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Public-safe resource for Department
 * 
 * Expone únicamente datos seguros sin información sensible
 */
class DepartmentPublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            
            // Sede relacionada
            'headOffice' => $this->when(
                $this->relationLoaded('headOffice'),
                fn() => [
                    'id' => $this->head_office_id,
                    'name' => $this->headOffice?->name,
                    'code' => $this->headOffice?->code,
                ]
            ),
            
            // Conteo de carreras
            'careersCount' => $this->when(
                $this->relationLoaded('careers'),
                fn() => $this->careers->count()
            ),
        ];
    }

    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'headOfficeName' => $this->headOffice?->name,
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'headOfficeName' => $this->headOffice?->name,
        ];
    }

    protected function getResourceType(): string
    {
        return 'publicDepartment';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->name,
                'code' => $item->code,
                'headOfficeId' => $item->head_office_id,
            ])->values(),
            'count' => $collection->count()
        ];
    }

    public function with(Request $request): array
    {
        return [];
    }
}
