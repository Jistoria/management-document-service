<?php

namespace App\Http\Resources;

use App\Constants\EntityType;
use App\Constants\TypeInput;
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
            'entityType' => $this->entity_type_id ? [
                'id' => $this->entity_type_id,
                'key' => EntityType::getKey($this->entity_type_id),
                'label' => EntityType::getLabel($this->entity_type_id),
            ] : null,
            'typeInputId' => $this->type_input_id,
            'typeInput' => $this->type_input_id ? [
                'id' => $this->type_input_id,
                'key' => TypeInput::getKey($this->type_input_id),
                'label' => TypeInput::getLabel($this->type_input_id),
            ] : null,
            'dataType' => $this->data_type,
            'schemaFieldId' => $this->pivot?->id,
            'isRequired' => $this->pivot?->is_required,
            'sortOrder' => $this->pivot?->sort_order,
            'defaultValue' => $this->pivot?->default_value,
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
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
