<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;

class RequestSetMobileRequest extends ApiRequest
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
            'mobile' => array('required','regex:/^09[0-9]{9}$/')
        ];
    }
}
