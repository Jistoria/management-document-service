<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class MetadataSchemaPublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'fields' => MetadataFieldPublicResource::collection($this->whenLoaded('metadataFields')),
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
