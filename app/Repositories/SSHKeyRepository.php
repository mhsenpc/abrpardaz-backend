<?php


namespace App\Repositories;


use App\Models\SshKey;

class SSHKeyRepository extends BaseRepository
{
    public function __construct(SshKey $model)
    {
        $this->model = $model;
    }

    function create(string $name, string $content, int $user_id)
    {
        $this->newQuery()->query->insert([
            'user_id' => $user_id,
            'name' => $name,
            'content' => $content,
        ]);
    }

    function edit(string $content){

    }
}
