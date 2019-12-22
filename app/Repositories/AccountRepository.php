<?php


namespace App\Repositories;


use App\Models\Account;

class AccountRepository extends BaseRepository
{
    function __construct(Account $model)
    {
        $this->model = $model;
    }
}
