<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Profile\RequestSetMobileRequest;
use App\Http\Requests\Profile\SetMobileRequest;
use App\Http\Requests\Profile\SetUserInfoRequest;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    /**
     * @var ProfileRepository
     */
    protected $repository;

    public function __construct(ProfileRepository $repository)
    {
        $this->repository = $repository;
    }

    function getUserInfo(){
        $user = Auth::user();
        $profile = Auth::user()->profile;
        return responder()->success([
            'user'    => $user
        ]);
    }

    function setUserBasicInfo(SetUserInfoRequest $request){
        $profile = Auth::user()->profile->update([
            'first_name' => \request('first_name'),
            'last_name' => \request('last_name'),
            'national_code' => \request('national_code'),
        ]);
    }

    function requestSetMobile(RequestSetMobileRequest $request){

    }

    function setMobile(SetMobileRequest $request){

    }
}
