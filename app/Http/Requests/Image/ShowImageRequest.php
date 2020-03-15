<?php

namespace App\Http\Requests\Image;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class ShowImageRequest extends ApiRequest
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
            'id' => 'required|numeric|exists:images,id',
        ];
    }
}
