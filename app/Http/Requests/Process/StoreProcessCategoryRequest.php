<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreProcessCategoryRequest extends BaseFormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('process_categories', 'name')->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('process_categories', 'code')->whereNull('deleted_at')
            ],
            'subsystem_id' => [
                'required',
                'string',
                'uuid',
                'exists:subsystems,id'
            ]
        ];
    }
}
