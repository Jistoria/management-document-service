<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequiredDocumentResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'isRequired' => $this->is_required,
            'order' => $this->order,
            'documentTypeId' => $this->document_type_id,
            'processId' => $this->process_id,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,
            
            // Conditional relationships
            'documentType' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'process' => $this->whenLoaded('process'),
        ];
    }
}
