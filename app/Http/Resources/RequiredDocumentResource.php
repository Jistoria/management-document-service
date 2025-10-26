<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RequiredDocumentResource extends BaseResource
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
            'processId' => $this->process_id,
            'documentTypeId' => $this->document_type_id,
            'metadataSchemaId' => $this->metadata_schema_id,
            'codeDefault' => $this->code_default,
            'urlResource' => $this->url_resource,
            'isPublic' => (bool) $this->is_public,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,

            'documentType' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'process' => new ProcessResource($this->whenLoaded('process')),
            'metadataSchema' => new MetadataSchemaResource($this->whenLoaded('metadataSchema')),
        ];
    }

    /**
     * Build context information for meta
     */
    protected function getMetaContext(Request $request): array
    {
        $context = ['basic'];
        if ($this->relationLoaded('documentType')) {
            $context[] = 'documentType';
        }
        if ($this->relationLoaded('process')) {
            $context[] = 'process';
        }
        if ($this->relationLoaded('metadataSchema')) {
            $context[] = 'metadataSchema';
        }
        if ($this->relationLoaded('academicRole')) {
            $context[] = 'academicRole';
        }
        if (isset($this->statistics)) {
            $context[] = 'statistics';
        }
        return $context;
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'documentTypeId' => $this->document_type_id,
            'processId' => $this->process_id,
            'order' => $this->order,
            'mandatory' => $this->mandatory,
            'codeDefault' => $this->code_default,
            'isPublic' => (bool) $this->is_public,
        ];
    }

    protected function getResourceType(): string
    {
        return 'required_document';
    }
}
