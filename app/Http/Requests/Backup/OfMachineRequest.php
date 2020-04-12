<?php

namespace App\Http\Requests\Backup;

use App\Http\Requests\ApiRequest;

class OfMachineRequest extends ApiRequest
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
            'machine_id' => 'required|numeric|exists:machines,id'
        ];
    }
}
