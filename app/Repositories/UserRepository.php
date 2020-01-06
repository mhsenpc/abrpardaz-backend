<?php


namespace App\Repositories;


use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\User";
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

    function activateUserByEmail(string $email)
    {
        User::where('email', $email)->update(['is_active' => true, 'email_verified_at' => Carbon::now()]);
    }

    function updatePassword(string $email,string $password){
        User::where('email', $email)->update(['password' =>$password]);
    }
}
