<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Profile\RequestSetMobileRequest;
use App\Http\Requests\Profile\RequestSetPhoneRequest;
use App\Http\Requests\Profile\SetMobileRequest;
use App\Http\Requests\Profile\SetPhoneRequest;
use App\Http\Requests\Profile\SetUserInfoRequest;
use App\Http\Requests\profile\UploadBirthCertificateRequest;
use App\Http\Requests\profile\UploadNationalCardBackRequest;
use App\Http\Requests\profile\UploadNationalCardFrontRequest;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use App\Services\MobileService;
use App\Services\PhoneService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    function getUserInfo()
    {
        $user = Auth::user();
        $profile = Auth::user()->profile;
        return responder()->success([
            'user' => $user
        ]);
    }

    function setUserBasicInfo(SetUserInfoRequest $request)
    {
        Profile::where('id', Auth::user()->profile_id)->update([
            'national_code' => \request('national_code'),
            'first_name' => \request('first_name'),
            'last_name' => \request('last_name')
        ]);

        return responder()->success(['message' => 'اطلاعات شما با موفقیت ذخیره شد']);
    }

    function requestSetMobile(RequestSetMobileRequest $request)
    {
        if (config('app.env') == 'local')
            $code = 11111;
        else
            $code = rand(11111, 99999);
        Cache::put('validation_code_for_' . request('mobile'), $code, 5 * 60);
        MobileService::sendActivationCode(request('mobile'), $code);

        return responder()->success(['message' => 'کد فعال سازی به شماره موبایل شما ارسال گردید']);
    }

    function setMobile(SetMobileRequest $request)
    {
        $code = Cache::get('validation_code_for_' . request('mobile'));
        if (\request('code') == $code) {
            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'mobile' => \request('mobile'),
                'mobile_verified_at' => Carbon::now()
            ]);

            return responder()->success(['message' => 'شماره موبایل شما با موفقیت ذخیره شد']);
        } else {
            return responder()->error(400, 'کد وارد شده صحیح نمی باشد');
        }
    }

    function requestSetPhone(RequestSetPhoneRequest $request)
    {
        if (config('app.env') == 'local')
            $code = 11111;
        else
            $code = rand(11111, 99999);
        Cache::put('validation_code_for_' . request('phone'), $code, 5 * 60);
        PhoneService::sendActivationCode(request('phone'), $code);

        return responder()->success(['message' => 'منتظر دریافت کد فعال سازی روی این شماره باشید']);
    }

    function setPhone(SetPhoneRequest $request)
    {
        $code = Cache::get('validation_code_for_' . request('phone'));
        if (\request('code') == $code) {
            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'phone' => \request('phone'),
                'phone_verified_at' => Carbon::now()
            ]);

            return responder()->success(['message' => 'شماره تلفن شما با موفقیت ذخیره شد']);
        } else {
            return responder()->error(400, 'کد وارد شده صحیح نمی باشد');
        }
    }

    function uploadNationalCardFront(UploadNationalCardFrontRequest $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_front' => $path,
            ]);

            return responder()->success(['message' => 'تصویر با موفقیت بارگذاری شد']);
        }
        else{
            return responder()->error(400,'بارگذاری تصویر مشکلی وجود دارد');
        }

    }

    function uploadNationalCardBack(UploadNationalCardBackRequest  $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_back' => $path,
            ]);

            return responder()->success(['message' => 'تصویر با موفقیت بارگذاری شد']);
        }
        else{
            return responder()->error(400,'بارگذاری تصویر مشکلی وجود دارد');
        }
    }

    function uploadBirthCertificate(UploadBirthCertificateRequest $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'birth_certificate' => $path,
            ]);


            return responder()->success(['message' => 'تصویر با موفقیت بارگذاری شد']);
        }
        else{
            return responder()->error(400,'بارگذاری تصویر مشکلی وجود دارد');
        }
    }
}
