<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Resource representation of a metadata field.
 */
class MetadataFieldResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'schemaId' => $this->schema_id,
            'name' => $this->name,
            'dataType' => $this->data_type,
            'isRequired' => $this->is_required,
            'defaultValue' => $this->getFormattedDefaultValue(),
            'validationRegex' => $this->validation_regex,
            'fieldOrder' => $this->field_order,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dataType' => $this->data_type,
        ];
    }

    protected function getResourceType(): string
    {
        return 'metadata_field';
    }
}
