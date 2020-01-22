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
use App\Services\MobileService;
use App\Services\PhoneService;
use App\Services\Responder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProfileController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Profile"},
     *      path="/profile/getUserInfo",
     *      summary="Get user basic info",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *
     *     )
     *
     */
    function getUserInfo()
    {
        $user = Auth::user();
        $profile = Auth::user()->profile;
        return Responder::result([
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/setUserBasicInfo",
     *      summary="Set user basic info",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="national_code",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function setUserBasicInfo(SetUserInfoRequest $request)
    {
        Profile::where('id', Auth::user()->profile_id)->update([
            'national_code' => \request('national_code'),
            'first_name' => \request('first_name'),
            'last_name' => \request('last_name')
        ]);

        return Responder::success('اطلاعات شما با موفقیت ذخیره شد');
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/requestSetMobile",
     *      summary="Request set mobile",
     *      description="It sends a code to the mobile number",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function requestSetMobile(RequestSetMobileRequest $request)
    {
        if (config('app.env') == 'local')
            $code = 11111;
        else
            $code = rand(11111, 99999);
        Cache::put('validation_code_for_' . request('mobile'), $code, 5 * 60);
        MobileService::sendActivationCode(request('mobile'), $code);

        return Responder::success('کد فعال سازی به شماره موبایل شما ارسال گردید');
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/setMobile",
     *      summary="Set mobile",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function setMobile(SetMobileRequest $request)
    {
        $code = Cache::get('validation_code_for_' . request('mobile'));
        if (\request('code') == $code) {
            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'mobile' => \request('mobile'),
                'mobile_verified_at' => Carbon::now()
            ]);

            return Responder::success('شماره موبایل شما با موفقیت ذخیره شد');
        } else {
            return Responder::error('کد وارد شده صحیح نمی باشد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/requestSetPhone",
     *      summary="Request set phone",
     *      description="It calls to the phone number and tells a code",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function requestSetPhone(RequestSetPhoneRequest $request)
    {
        if (config('app.env') == 'local')
            $code = 11111;
        else
            $code = rand(11111, 99999);
        Cache::put('validation_code_for_' . request('phone'), $code, 5 * 60);
        PhoneService::sendActivationCode(request('phone'), $code);

        return Responder::success('منتظر دریافت کد فعال سازی روی این شماره باشید');
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/setPhone",
     *      summary="Set phone",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function setPhone(SetPhoneRequest $request)
    {
        $code = Cache::get('validation_code_for_' . request('phone'));
        if (\request('code') == $code) {
            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'phone' => \request('phone'),
                'phone_verified_at' => Carbon::now()
            ]);

            return Responder::success('شماره تلفن شما با موفقیت ذخیره شد');
        } else {
            return Responder::error('کد وارد شده صحیح نمی باشد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/uploadNationalCardFront",
     *      summary="Upload national card front",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="image",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function uploadNationalCardFront(UploadNationalCardFrontRequest $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_front' => $path,
            ]);

            return Responder::success('تصویر با موفقیت بارگذاری شد');
        }
        else{
            return Responder::error('در بارگذاری تصویر مشکلی وجود دارد');
        }

    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/uploadNationalCardBack",
     *      summary="Upload national card back",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="image",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function uploadNationalCardBack(UploadNationalCardBackRequest  $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_back' => $path,
            ]);

            return Responder::success('تصویر با موفقیت بارگذاری شد');
        }
        else{
            return Responder::error('در بارگذاری تصویر مشکلی وجود دارد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/uploadBirthCertificate",
     *      summary="Upload birth certificate",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="image",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function uploadBirthCertificate(UploadBirthCertificateRequest $request){
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'birth_certificate' => $path,
            ]);


            return Responder::success('تصویر با موفقیت بارگذاری شد');
        }
        else{
            return Responder::error('در بارگذاری تصویر مشکلی وجود دارد');
        }
    }
}
