<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class SetUserInfoRequest extends ApiRequest
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
            'first_name' => 'required',
            'last_name' => 'required',
            'national_code' => 'required',
        ];
    }
}
