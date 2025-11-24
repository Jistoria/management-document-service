<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class MetadataSchemaResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'metadataFields' => MetadataFieldResource::collection($this->whenLoaded('metadataFields')),
        );
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }

    protected function getResourceType(): string
    {
        return 'metadata_schema';
    }
}

