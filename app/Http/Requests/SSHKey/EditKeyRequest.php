<?php

namespace App\Http\Requests\SSHKey;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class EditKeyRequest extends ApiRequest
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
            'id' => 'required|numeric',
            'name' => 'required',
            'content' => 'required'
        ];
    }
}
