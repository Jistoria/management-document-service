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
            'academicRoleId' => $this->academic_role_id,
            'metadataSchemaId' => $this->metadata_schema_id,
            'order' => $this->order,
            'mandatory' => $this->mandatory,
            'externalUserId' => $this->external_user_id,
            'externalOrganizationId' => $this->external_organization_id,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),

            // Conditional relationships
            'documentType' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'process' => new ProcessResource($this->whenLoaded('process')),
            'academicRole' => $this->whenLoaded('academicRole'),
            'metadataSchema' => $this->whenLoaded('metadataSchema'),

            // Statistics (added via resolveIncludes)
            'statistics' => $this->when(isset($this->statistics), $this->statistics),

            // Meta information
            'meta' => $this->when($request->get('include_meta', false), [
                'resourceType' => 'required_document',
                'generatedAt' => now()->toISOString(),
                'context' => $this->getMetaContext($request)
            ])
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
        ];
    }

    protected function getResourceType(): string
    {
        return 'required_document';
    }
}
