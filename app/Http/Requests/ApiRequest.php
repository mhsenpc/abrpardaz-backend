<?php


namespace App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $response = new ValidationResponse();
        foreach ($validator->errors()->toArray() as $key => $item) {
            $response->errors[$key] = reset($item);
        }

        throw new HttpResponseException(
            response()->json($response)
        );
    }
}

class ValidationResponse
{
    public $success = false;
    public $code = 400;
    public $message = 'در اعتبارسنجی اطلاعات مشکلی وجود دارد';
    public $errors = [];
}
