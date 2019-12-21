<?php


namespace App\Repositories;


use App\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new Invoice());
    }
}
