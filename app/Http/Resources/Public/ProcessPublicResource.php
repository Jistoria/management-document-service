<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Resource para exponer procesos de forma pública (sin autenticación)
 * Solo incluye campos seguros sin información sensible
 * 
 * @mixin \App\Models\Process
 */
class ProcessPublicResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'processCategoryId' => $this->process_category_id,
            'parentId' => $this->parent_id,
            'name' => $this->name,
            'code' => $this->code,
            'order' => $this->order,
            // Relaciones opcionales
            'processCategory' => $this->when(
                $this->relationLoaded('processCategory'),
                fn () => new ProcessCategoryPublicResource($this->processCategory)
            ),
            'parent' => $this->when(
                $this->relationLoaded('parent'),
                fn () => new ProcessPublicResource($this->parent)
            ),
            'subProcesses' => self::collection($this->whenLoaded('children')),
            
            // NO exponer: created_by, updated_by, created_at, updated_at, deleted_at
        ];
    }

    /**
     * Get minimal fields for listing views
     */
    public function minimal(): array
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

    /**
     * Get minimal fields for base resource strategy
     */
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

    /**
     * Get resource type identifier
     */
    protected function getResourceType(): string
    {
        return 'publicProcess';
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
                'processCategoryId' => $item->process_category_id,
                'parentId' => $item->parent_id,
                'order' => $item->order,
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
