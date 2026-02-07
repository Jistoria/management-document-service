<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Public-safe resource for Career
 * 
 * Expone únicamente datos seguros sin información sensible
 */
class CareerPublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,

            // Departamento relacionado
            'department' => $this->when(
                $this->relationLoaded('department'),
                fn() => [
                    'id' => $this->department_id,
                    'name' => $this->department?->name,
                    'code' => $this->department?->code,
                    'codeNumeric' => $this->department?->code_numeric,
                ]
            ),

            // Sede relacionada
            'headOffice' => $this->when(
                $this->relationLoaded('headOffice'),
                fn() => [
                    'id' => $this->head_office_id,
                    'name' => $this->headOffice?->name,
                    'code' => $this->headOffice?->code,
                    'codeNumeric' => $this->headOffice?->code_numeric,
                ]
            ),
        ];
    }

    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,
            'departmentName' => $this->department?->name,
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,
            'departmentName' => $this->department?->name,
        ];
    }

    protected function getResourceType(): string
    {
        return 'publicCareer';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->name,
                'code' => $item->code,
                'codeNumeric' => $item->code_numeric,
                'departmentId' => $item->department_id,
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
