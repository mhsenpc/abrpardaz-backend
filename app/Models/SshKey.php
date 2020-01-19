<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SshKey extends Model
{
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }

    protected $guarded = [];

    static function createKey(string $name, string $content, int $user_id)
    {
        return static::create([
            'user_id' => $user_id,
            'name' => $name,
            'content' => $content,
        ]);
    }

    function edit(string $content){
        $this->content = $content;
        $this->save();
        return $this;
    }
}
