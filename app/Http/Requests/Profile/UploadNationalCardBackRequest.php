<?php

namespace App\Http\Requests\profile;

use App\Http\Requests\ApiRequest;

class UploadNationalCardBackRequest extends ApiRequest
{
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
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
