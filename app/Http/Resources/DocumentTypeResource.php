<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentTypeResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,
            
            // Conditional relationships
            'requiredDocuments' => RequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
            'requiredDocumentsCount' => $this->when(
                $this->relationLoaded('requiredDocuments'),
                fn() => $this->requiredDocuments->count()
            ),
            
            // Statistics (added via resolveIncludes)
            'statistics' => $this->when(
                isset($this->statistics),
                $this->statistics ?? null
            ),

            // Meta information
            'meta' => $this->when($request->get('include_meta', false), [
                'resourceType' => 'document_type',
                'generatedAt' => now()->toISOString(),
                'context' => $this->getMetaContext($request)
            ])
        ];
    }

    /**
     * Get context information for meta
     */
    protected function getMetaContext(Request $request): array
    {
        $context = ['basic'];
        
        if ($this->relationLoaded('requiredDocuments')) {
            $context[] = 'requiredDocuments';
        }
        
        if (isset($this->statistics)) {
            $context[] = 'statistics';
        }

        return $context;
    }
}
