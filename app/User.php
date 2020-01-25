<?php

namespace App;

use App\Models\Machine;
use App\Models\Profile;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public function project()
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
        $user->save();

        $project->owner_id = $user->id;
        $project->save();

        $user->project()->attach($project->id);

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
