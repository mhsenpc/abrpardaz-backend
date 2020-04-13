<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;

class SetPhoneRequest extends ApiRequest
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
            'phone' => array('required','regex:/^0[1|2|3|4|5|6|7|8][0-9]{9}$/'),
            'code' => 'required'
        ];
    }
}
