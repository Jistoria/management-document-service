<?php

namespace App\Http\Requests\Subsystem;

use App\Helpers\SubsystemEntityMap;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;


class DeleteEntityLinkRequest extends BaseFormRequest
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
            'entity_type' => ['required', Rule::in(SubsystemEntityMap::keys())],
            'entity_id' => ['required', 'exists:subsystem_entity_links,entity_id'],
            'subsystem_id' => ['required', 'exists:subsystems,id'],
        ];
    }
}
