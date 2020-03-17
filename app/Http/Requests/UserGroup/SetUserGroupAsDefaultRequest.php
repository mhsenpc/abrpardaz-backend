<?php

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class SetUserGroupAsDefaultRequest extends ApiRequest
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
            'id' => 'required|numeric|exists:user_groups,id',
        ];
    }
}
