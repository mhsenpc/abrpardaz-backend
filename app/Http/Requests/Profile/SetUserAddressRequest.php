<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class SetUserAddressRequest extends ApiRequest
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
            'postal_code' => 'required|numeric|digits:10',
            'address' => 'required',
        ];
    }
}
