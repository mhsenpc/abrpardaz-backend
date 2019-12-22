<?php


namespace App\Repositories;


use App\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    function __construct(Transaction $model)
    {
        $this->model = $model;
    }
}
