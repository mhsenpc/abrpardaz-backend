<?php

namespace App\Http\Requests\Volume;

use Illuminate\Foundation\Http\FormRequest;

class CreateNewVolumeRequest extends FormRequest
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
            'size' => 'required',
            'machine_id' => 'required|numeric|exists:machines,id'
        ];
    }
}
