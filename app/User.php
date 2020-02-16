<?php

namespace App;

use App\Models\Machine;
use App\Models\Profile;
use App\Models\Project;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'is_root', 'profile_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_billing_date'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    function getDefaultProject()
    {
        return Project::where('owner_id', $this->id)->first();
    }

    function __toString()
    {
        return $this->profile->first_name ;
    }

    function updateLastBillingDate(){
        $this->last_billing_date = Carbon::now();
        $this->save();
        return $this;
    }

    static function newUser(string $email, string $password)
    {
        $project = (new Project());
        $project->name = "Default";
        $project->save();

        $profile = (new Profile());
        $profile->save();

        $user = new User();
        $user->password = $password;
        $user->is_active = false;
        $user->email = $email;
        $user->profile_id = $profile->id;
        $user->last_billing_date = Carbon::now();
        $user->user_group_id = UserGroup::findDefaultGroup()->id;
        $user->save();

        $project->owner_id = $user->id;
        $project->save();

        $user->projects()->attach($project->id);

        return $user;
    }

    static function activateUserByEmail(string $email)
    {
        User::where('email', $email)->update(['is_active' => true, 'email_verified_at' => Carbon::now()]);
    }

    static function updatePassword(string $email, string $password)
    {
        User::where('email', $email)->update(['password' => $password]);
    }
}
