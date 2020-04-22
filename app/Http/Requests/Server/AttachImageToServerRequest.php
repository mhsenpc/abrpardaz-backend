<?php

namespace App\Http\Requests\Server;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class AttachImageToServerRequest extends ApiRequest
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
            'id' => 'required|numeric|exists:machines,id',
            'image_id' => 'required|numeric|exists:images,id'
        ];
    }
}
