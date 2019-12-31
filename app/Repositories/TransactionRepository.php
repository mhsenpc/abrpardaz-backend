<?php


namespace App\Repositories;


use App\Models\Transaction;
use Prettus\Repository\Eloquent\BaseRepository;

class TransactionRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Transaction";
    }
}
