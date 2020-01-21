<?php


namespace App\Http\Requests;


use App\Services\Responder;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        foreach ($validator->errors()->toArray() as $key => $item) {
            $errors[$key] = reset($item);
        }

        throw new HttpResponseException(
            Responder::validationError($errors)
        );
    }
}
