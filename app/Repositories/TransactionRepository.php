<?php


namespace App\Repositories;


use App\Models\transaction;

class TransactionRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new transaction());
    }
}
