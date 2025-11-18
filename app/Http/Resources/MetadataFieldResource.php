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
            'fieldKey' => $this->field_key,
            'label' => $this->label,
            'entityTypeId' => $this->entity_type_id,
            'typeInputId' => $this->type_input_id,
            'dataType' => $this->data_type,
            'isReference' => $this->is_reference,
            'referenceEntity' => $this->reference_entity,
            'referenceColumn' => $this->reference_column,
            'schemaFieldId' => $this->pivot?->id,
            'isRequired' => $this->pivot?->is_required,
            'sortOrder' => $this->pivot?->sort_order,
            'defaultValue' => $this->pivot?->default_value,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'fieldKey' => $this->field_key,
            'label' => $this->label,
            'dataType' => $this->data_type,
        ];
    }

    protected function getResourceType(): string
    {
        return 'metadata_field';
    }
}
