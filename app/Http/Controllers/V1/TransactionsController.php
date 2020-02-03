<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\Responder;

class TransactionsController extends BaseController
{
    function index()
    {
        return Responder::result(['list'=> Transaction::with('invoice')->get()]);
    }
}
