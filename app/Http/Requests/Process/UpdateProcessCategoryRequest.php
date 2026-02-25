<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcessCategoryRequest extends BaseFormRequest
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
        $processCategoryId = $this->route('category');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('process_categories', 'name')->ignore($processCategoryId)->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                Rule::unique('process_categories', 'code')->ignore($processCategoryId)->whereNull('deleted_at')
            ]
        ];
    }
}
