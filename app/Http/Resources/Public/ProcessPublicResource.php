<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para exponer procesos de forma pública (sin autenticación)
 * Solo incluye campos seguros sin información sensible
 * 
 * @mixin \App\Models\Process
 */
class ProcessPublicResource extends JsonResource
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
        ];
    }
}
