<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class EditPlanRequest extends ApiRequest
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
            'id' => 'required|numeric|exists:plans,id',
            'remote_id' => 'required',
            'name' => 'required',
            'disk' => 'required|numeric',
            'ram' => 'required|numeric',
            'vcpu' => 'required|numeric',
            'hourly_price' => 'required|numeric',
        ];
    }
}
