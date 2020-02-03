<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Models\Invoice;
use App\Services\Responder;

class InvoiceController extends BaseController
{
    function index()
    {
        return Responder::result(['list'=> Invoice::all()]);
    }
}
