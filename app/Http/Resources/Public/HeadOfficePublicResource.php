<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Public-safe resource for Head Office
 * 
 * Expone únicamente datos seguros sin información sensible
 * Para uso en endpoints públicos sin autenticación
 */
class HeadOfficePublicResource extends BaseResource
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
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,

            // Relaciones solo si están cargadas
            'departmentsCount' => $this->when(
                $this->relationLoaded('departments'),
                fn() => $this->departments->count()
            ),

            // NO exponer: created_by, updated_by, created_at, updated_at, version, deleted_at
        ];
    }

    /**
     * Get minimal fields for listing views
     */
    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,
        ];
    }

    /**
     * Get minimal fields for base resource strategy
     */
    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeNumeric' => $this->code_numeric,
            'name' => $this->name,
        ];
    }

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'publicHeadOffice';
    }

    /**
     * Transform for dropdown/select usage
     */
    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->name,
                'code' => $item->code,
                'codeNumeric' => $item->code_numeric,
            ])->values(),
            'count' => $collection->count()
        ];
    }

    /**
     * Override to prevent exposing metadata
     */
    public function with(Request $request): array
    {
        return []; // No metadata for public access
    }
}
