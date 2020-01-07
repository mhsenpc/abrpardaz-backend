<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class NewTicketRequest extends ApiRequest
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
            'title' => 'required',
            'priority' => 'required',
            'message' => 'required',
            'machine' => 'sometimes|numeric|exists:machines,id',
            'category' => 'required|numeric|exists:categories,id'
        ];
    }
}
