<?php

namespace App\Http\Requests\Subsystem;

use App\Helpers\SubsystemEntityMap;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateSubsystemEntityLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'entities' => ['required', 'array', 'min:1'],

            'entities.*.entity_type' => [
                'required',
                Rule::in(SubsystemEntityMap::keys())
            ],

            'entities.*.entity_ids' => ['required', 'array', 'min:1'],

            'entities.*.entity_ids.*' => [
                'uuid',
                function ($attribute, $value, $fail) {
                    // Extraer el índice actual del array (ej: entities.0.entity_ids.1 → 0)
                    preg_match('/entities\.(\d+)\.entity_ids/', $attribute, $matches);
                    $index = $matches[1] ?? null;

                    // Obtener el entity_type correspondiente a este grupo
                    $entityType = data_get($this->input("entities.$index"), 'entity_type');

                    if (!SubsystemEntityMap::isValidKey($entityType)) {
                        return $fail("Entity type \"{$entityType}\" is not allowed.");
                    }

                    // Obtener la tabla correspondiente según el mapa
                    $table = SubsystemEntityMap::getTable($entityType);

                    // Verificar si el UUID existe en esa tabla
                    if (!DB::table($table)->where('id', $value)->exists()) {
                        return $fail("El ID \"{$value}\" no existe en la entidad \"{$entityType}\".");
                    }
                },
            ],
        ];
    }

}
