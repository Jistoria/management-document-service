<?php

namespace App\Http\Requests;

use App\Constants\HttpStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{

    public function __construct(
        protected ApiResponse $apiResponse,
    ) {}

    /**
     * Manejar un error de validación y formatear la respuesta.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->apiResponse->error(
                'validation_error',
                HttpStatus::UNPROCESSABLE_ENTITY,
                $validator->errors(),
            )
        );
    }
}