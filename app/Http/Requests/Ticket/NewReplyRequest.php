<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\ApiRequest;
use App\Traits\AddIDParameterTrait;

class NewReplyRequest extends ApiRequest
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
            'comment' => 'required',
            'id' => 'required|numeric|exists:tickets,id'
        ];
    }
}
