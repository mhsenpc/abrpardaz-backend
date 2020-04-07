<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class UploadReceiptRequest extends ApiRequest
{
    use AddIDParameterTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:invoices,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description' => 'required'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.image' => 'فرمت تصویر بارگذاری شده معتبر نمی باشد',
        ];
    }
}
