<?php


namespace App\Repositories;


use App\Models\Account;

class AccountRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new Account());
    }
}
