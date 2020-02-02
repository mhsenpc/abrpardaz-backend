<?php

namespace App\Http\Requests\Server;

use App\Http\Requests\ApiRequest;

class CreateFromImageRequest extends ApiRequest
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
            'name' => 'required',
            'plan_id' => 'required|numeric|exists:plans,id',
            'image_id' => 'required|numeric|exists:images,id',
            'ssh_key_id' => 'sometimes|nullable|numeric|exists:ssh_keys,id',
            'project_id' => 'required|numeric|exists:projects,id',
        ];
    }
}
