<?php

namespace App\Http\Requests\Subsystem;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;


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
            'entity_type' => ['required', 'exists:subsystem_entity_links,entity_type'],
            'entity_id' => ['required', 'exists:subsystem_entity_links,entity_id'],
            'subsystem_id' => ['required', 'exists:subsystems,id'],
        ];
    }
}
