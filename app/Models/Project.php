<?php

namespace App\Models;

use App\Scopes\ProjectAccessScope;
use App\Services\IdentityService;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(\App\User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ProjectAccessScope());
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public static function createProject(string $email, string $name, int $owner_id): Project
    {
        $user = User::find($owner_id);

        $identity = new IdentityService();
        $remote_project = $identity->createProject("$email-$name",$user->remote_user_id);

        $project = new Project();
        $project->name = $name;
        $project->remote_id = $remote_project->id;
        $project->owner_id = $owner_id;
        $project->save();

        User::find($owner_id)->projects()->attach($project->id);

        return $project;
    }

    public function updateOwner(int $owner_id)
    {
        $this->owner_id = $owner_id;
        $this->save();
        return $this;
    }
}
