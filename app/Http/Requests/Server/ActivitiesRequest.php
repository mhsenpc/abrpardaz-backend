<?php

namespace App\Http\Requests\Server;

use App\Traits\AddIDParameterTrait;
use Illuminate\Foundation\Http\FormRequest;

class ActivitiesRequest extends FormRequest
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
            'id' => 'required|numeric|exists:machines,id'
        ];
    }
}
