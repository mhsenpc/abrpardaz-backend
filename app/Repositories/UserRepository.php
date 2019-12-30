<?php


namespace App\Repositories;


use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class UserRepository extends BaseRepository
{
    function __construct(User $model)
    {
        $this->model = $model;
    }

    function newUser(string $email, string $password)
    {
        $project = (new Project());
        $project->name = "Default";
        $project->save();

        $profile = (new Profile());
        $profile->save();

        $this->model->password = $password;
        $this->model->is_active = false;
        $this->model->email = $email;
        $this->model->profile_id = $profile->id;
        $this->model->save();

        $project->owner_id = $this->model->id;
        $project->save();

        $this->model->project()->attach($project->id);

        return $this->model;
    }

    function activateUserByEmail(string $email){
        User::where('email',$email)->update(['is_active' => true, 'email_verified_at'=>Carbon::now()]);
    }
}
