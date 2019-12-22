<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Payment\PaymentResultRequest;
use App\Http\Requests\Payment\RequestPaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    function requestPayment(RequestPaymentRequest $request){

    }

    function paymentResult(PaymentResultRequest $request){

    }
}
