<?php

namespace App\Http\Requests\Subsystem;

use App\Helpers\SubsystemEntityMap;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreEntityLinkRequest extends BaseFormRequest
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
            'subsystem_id' => 'required|uuid|exists:subsystems,id',
            'entity_type' => ['required', Rule::in(SubsystemEntityMap::keys())],
            'entity_id' => ['required',
                'uuid',
                function ($attribute, $value, $fail) {
                    $entityType = $this->input('entity_type');

                    if (!SubsystemEntityMap::isValidKey($entityType)) {
                        return $fail ("Entity type \"{$entityType}\" is not allowed.");
                    }

                    $table = SubsystemEntityMap::getTable($entityType);

                    if (!DB::table($table)->where('id', $value)->exists()) {
                        return $fail("El ID no existe en la entidad \"{$entityType}\".");
                    }
                },
            ],
        ];
    }

}
