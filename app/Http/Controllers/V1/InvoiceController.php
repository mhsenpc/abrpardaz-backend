<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Invoices\ConfirmReceiptRequest;
use App\Http\Requests\Invoices\ShowInvoiceRequest;
use App\Http\Requests\Invoices\UploadReceiptRequest;
use App\Models\Invoice;
use App\Services\Responder;

class InvoiceController extends BaseController
{
    function __construct()
    {
        $this->middleware('permission:Invoice Operator', ['only' => ['show','confirmReceipt']]);
    }

    function index()
    {
        return Responder::result(['list' => Invoice::all()]);
    }

    function show(ShowInvoiceRequest $request)
    {
        $invoice = Invoice::find(request('id'));
        if (!empty($invoice->receipt)) {
            $invoice->receipt = asset('storage/' . $invoice->receipt);
        }
        return Responder::result(['item' => $invoice]);
    }

    function uploadReceipt(UploadReceiptRequest $request)
    {
        $path = $request->file('image')->store('receipts');
        $invoice = Invoice::find(request('id'));
        $invoice->receipt = $path;
        $invoice->description = request('description');
        $invoice->save();
        return Responder::success('اطلاعات فیش شما با موفقیت ذخیره شد');
    }

    function confirmReceipt(ConfirmReceiptRequest $request){
        $invoice = Invoice::find(request('id'));
        $invoice->is_paid = true;
        $invoice->save();
        return Responder::success('فاکتور با موفقیت تایید شد و بعنوان پرداخت شده علامت گذاری شد');
    }
}
