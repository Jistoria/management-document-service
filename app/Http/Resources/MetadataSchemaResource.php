<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class MetadataSchemaResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parentSchemaId' => $this->parent_schema_id,
            'isCanonical' => $this->is_canonical,
            'version' => $this->version,
            'externalSystemId' => $this->external_system_id,
            'apiEndpoint' => $this->api_endpoint,
            'cacheTtl' => $this->cache_ttl,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
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

