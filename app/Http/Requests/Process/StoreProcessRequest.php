<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreProcessRequest extends BaseFormRequest
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
            'processCategoryId' => [
                'required',
                'string',
                'uuid',
                'exists:process_categories,id'
            ],
            'parentId' => [
                'nullable',
                'string',
                'uuid'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('processes', 'code')->whereNull('deleted_at')
            ],
            'order' => [
                'nullable',
                'integer',
                'min:0'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'process_category_id' => 'categoría de proceso',
            'parent_id' => 'proceso padre',
            'name' => 'nombre',
            'code' => 'código',
            'order' => 'orden',
        ];
    }
}
