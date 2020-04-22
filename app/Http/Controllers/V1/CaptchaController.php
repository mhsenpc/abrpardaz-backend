<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;

class CaptchaController extends BaseController
{
    public function generate()
    {
        return $this->response->array([
            'status_code' => '200',
            'message' => 'created succeed',
            'url' => app('captcha')->create('default', true)
        ]);
    }

}
