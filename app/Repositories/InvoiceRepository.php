<?php


namespace App\Repositories;


use App\Models\Invoice;
use Prettus\Repository\Eloquent\BaseRepository;

class InvoiceRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\invoice";
    }
}
