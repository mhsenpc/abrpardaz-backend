<?php

namespace App\Http\Requests\Volume;

use App\Http\Requests\AddIDParameterTrait;
use Illuminate\Foundation\Http\FormRequest;

class AttachToMachineRequest extends FormRequest
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
            'id' => 'required|numeric|exists:volumes,id',
            'machine_id' => 'required|numeric|exists:machines,id'
        ];
    }
}
