<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class RequestPaymentRequest extends ApiRequest
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
            'user_id' => 'required',
            'amount' => 'required'
        ];
    }
}
