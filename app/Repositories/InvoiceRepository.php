<?php


namespace App\Repositories;


use App\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    function __construct(Invoice $model)
    {
        $this->model = $model;
    }
}
