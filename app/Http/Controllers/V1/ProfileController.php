<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Profile\RequestSetMobileRequest;
use App\Http\Requests\Profile\SetMobileRequest;
use App\Http\Requests\Profile\SetUserInfoRequest;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;

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

    }

    function setUserInfo(SetUserInfoRequest $request){

    }

    function requestSetMobile(RequestSetMobileRequest $request){

    }

    function setMobile(SetMobileRequest $request){

    }
}
