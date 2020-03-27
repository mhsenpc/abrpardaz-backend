<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class ChangeRoleRequest extends ApiRequest
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
            'id' => 'required|numeric|exists:users,id',
            'role_id' => 'required|numeric|exists:roles,id',
        ];
    }
}
