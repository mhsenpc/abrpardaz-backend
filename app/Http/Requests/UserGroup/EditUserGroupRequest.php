<?php

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class EditUserGroupRequest extends ApiRequest
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
            'name' => 'required',
            'max_machines' => 'required|numeric',
            'max_snapshots' => 'required|numeric',
            'max_volumes_usage' => 'required|numeric',
        ];
    }
}
