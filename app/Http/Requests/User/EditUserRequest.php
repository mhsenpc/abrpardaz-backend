<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class EditUserRequest extends ApiRequest
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
            'email' => 'required',
            'is_active' => 'required',
            'referrer_id' => 'sometimes|nullable|numeric',
        ];
    }
}
