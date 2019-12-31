<?php


namespace App\Repositories;


use App\Models\SshKey;
use Prettus\Repository\Eloquent\BaseRepository;

class SSHKeyRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\SshKey";
    }

    function createKey(string $name, string $content, int $user_id)
    {
        $this->create([
            'user_id' => $user_id,
            'name' => $name,
            'content' => $content,
        ]);
    }

    function edit(string $content){

    }
}
