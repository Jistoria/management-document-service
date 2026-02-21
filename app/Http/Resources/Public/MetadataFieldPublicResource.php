<?php

namespace App\Http\Resources\Public;

use App\Constants\EntityType;
use App\Constants\TypeInput;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class MetadataFieldPublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        // Obtener el pivot del primer esquema relacionado (cuando se filtra por schema_id)
        $pivot = $this->relationLoaded('metadataSchemas') && $this->metadataSchemas->isNotEmpty()
            ? $this->metadataSchemas->first()->pivot
            : $this->pivot;

        $label = $pivot?->label ?? $this->label ?? 'set label';

        return [
            'id' => $this->id,
            'fieldKey' => $this->field_key,
            'label' => $label,
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

            // Pivot data (cuando se filtra por schema_id o está disponible)
            'schemaFieldId' => $pivot?->id,
            'isRequired' => $pivot?->is_required,
            'isRepeatable' => $pivot?->is_repeatable,
            'minOccurs' => $pivot?->min_occurs,
            'maxOccurs' => $pivot?->max_occurs,
            'allowDuplicates' => $pivot?->allow_duplicates,
            'sortOrder' => $pivot?->sort_order,
            'defaultValue' => $pivot?->default_value,
            'regexPattern' => $pivot?->regex_pattern,
            'validationErrorMessage' => $pivot?->validation_error_message,
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