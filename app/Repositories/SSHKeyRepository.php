<?php


namespace App\Repositories;


use App\Models\SSHKey;

class SSHKeyRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new SSHKey());
    }
}
