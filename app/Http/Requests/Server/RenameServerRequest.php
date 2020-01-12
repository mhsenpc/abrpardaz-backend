<?php

namespace App\Http\Requests\Server;

use App\Http\Requests\AddIDParameterTrait;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class RenameServerRequest extends ApiRequest
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
            'name' => 'required',
            'id' => 'required|numeric|exists:machines,id'
        ];
    }
}
