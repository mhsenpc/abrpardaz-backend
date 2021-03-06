<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class SetUserInfoRequest extends ApiRequest
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
            'first_name' => 'required',
            'last_name' => 'required',
            'organization_name' => 'sometimes|nullable',
            'national_code' => 'required|numeric|digits:10|unique:profiles,national_code,'. $this->user()->id,
        ];
    }
}
