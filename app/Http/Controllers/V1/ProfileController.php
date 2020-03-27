<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\profile\InvalidateBCRequest;
use App\Http\Requests\profile\InvalidateNCBackRequest;
use App\Http\Requests\profile\InvalidateNCFrontRequest;
use App\Http\Requests\profile\InvalidateProfileRequest;
use App\Http\Requests\Profile\RequestSetMobileRequest;
use App\Http\Requests\Profile\RequestSetPhoneRequest;
use App\Http\Requests\Profile\SetMobileRequest;
use App\Http\Requests\Profile\SetPhoneRequest;
use App\Http\Requests\Profile\SetUserInfoRequest;
use App\Http\Requests\profile\UploadBirthCertificateRequest;
use App\Http\Requests\profile\UploadNationalCardBackRequest;
use App\Http\Requests\profile\UploadNationalCardFrontRequest;
use App\Http\Requests\profile\ValidateBCRequest;
use App\Http\Requests\profile\ValidateNCBackRequest;
use App\Http\Requests\profile\ValidateNCFrontRequest;
use App\Http\Requests\profile\ValidateProfileRequest;
use App\Models\Profile;
use App\Services\MobileService;
use App\Services\PhoneService;
use App\Services\Responder;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $notifications = Auth::user()->unreadNotifications->count();
        $profile = Auth::user()->profile;
        return Responder::result([
            'user' => $user,
            'notifications' => $notifications
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
     *     @OA\Parameter(
     *         name="postal_code",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="address",
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
            'last_name' => \request('last_name'),
            'postal_code' => \request('postal_code'),
            'address' => \request('address'),
        ]);

        Log::info('set user basic info user #' . Auth::id());
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

        Log::info('request set mobile .mobile #' . request('mobile') . ' ,user #' . Auth::id());
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

            Log::info('successful set mobile .mobile #' . request('mobile') . ' ,user #' . Auth::id());
            return Responder::success('شماره موبایل شما با موفقیت ذخیره شد');
        } else {
            Log::warning('failed to match token for set mobile.mobile #' . request('mobile') . ' token #' . request('token') . ' ,user #' . Auth::id());
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

        Log::info('request set phone .phone #' . request('phone') . ' ,user #' . Auth::id());
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

            Log::info('successful set phone .phone #' . request('phone') . ' ,user #' . Auth::id());
            return Responder::success('شماره تلفن شما با موفقیت ذخیره شد');
        } else {
            Log::warning('failed to match token for set phone.phone #' . request('phone') . ' token #' . request('token') . ' ,user #' . Auth::id());
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
    function uploadNationalCardFront(UploadNationalCardFrontRequest $request)
    {
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_front' => $path,
            ]);

            Log::info('national card front uploaded.user #' . Auth::id());

            return Responder::success('تصویر با موفقیت بارگذاری شد');
        } else {
            Log::warning('failed to upload national card front.user #' . Auth::id());
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
    function uploadNationalCardBack(UploadNationalCardBackRequest $request)
    {
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'national_card_back' => $path,
            ]);

            Log::info('national card back uploaded.user #' . Auth::id());
            return Responder::success('تصویر با موفقیت بارگذاری شد');
        } else {
            Log::warning('failed to upload national card back .user #' . Auth::id());
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
    function uploadBirthCertificate(UploadBirthCertificateRequest $request)
    {
        if ($request->has('image')) {
            $path = $request->file('image')->store('images');

            $user = Auth::user();
            Profile::where('id', $user->profile_id)->update([
                'birth_certificate' => $path,
            ]);

            Log::info('birth certificate uploaded.user #' . Auth::id());
            return Responder::success('تصویر با موفقیت بارگذاری شد');
        } else {
            Log::warning('failed to upload birth certificate .user #' . Auth::id());
            return Responder::error('در بارگذاری تصویر مشکلی وجود دارد');
        }
    }

    function validateProfile(ValidateProfileRequest $request)
    {
        User::find(request('id'))->profile->validateProfile();
        //TODO: notify to user
        Log::info('profile validated.user #' . request('id'));
        return Responder::success('پروفایل با موفقیت تایید شد');
    }

    function invalidateProfile(InvalidateProfileRequest $request)
    {
        User::find(request('id'))->profile->invalidateProfile();
        //TODO: notify to user
        Log::info('profile validated.user #' . request('id'));
        return Responder::success('پروفایل با موفقیت به حالت تایید نشده تبدیل شد');
    }

    function validateNCFront(ValidateNCFrontRequest $request)
    {
        User::find(request('id'))->profile->validateNCFront();
        Log::info('profile NCFront validated.user #' . request('id'));
        return Responder::success('تصویر جلوی کارت ملی تایید شد');
    }

    function invalidateNCFront(InvalidateNCFrontRequest $request)
    {
        User::find(request('id'))->profile->invalidateNCFront();
        //TODO: notify to user
        Log::info('profile NCFront invalidated.user #' . request('id'));
        return Responder::success('تصویر جلوی کارت ملی رد شد');
    }

    function validateNCBack(ValidateNCBackRequest $request)
    {
        User::find(request('id'))->profile->validateNCBack();
        Log::info('profile NCBack validated.user #' . request('id'));
        return Responder::success('تصویر پشت کارت ملی تایید شد');
    }

    function invalidateNCBack(InvalidateNCBackRequest $request)
    {
        User::find(request('id'))->profile->invalidateNCBack();
        //TODO: notify to user
        Log::info('profile NCBack invalidated.user #' . request('id'));
        return Responder::success('تصویر پشت کارت ملی رد شد');
    }

    function validateBC(ValidateBCRequest $request)
    {
        User::find(request('id'))->profile->validateBC();
        Log::info('profile BC validated.user #' . request('id'));
        return Responder::success('تصویر شناسنامه تایید شد');
    }

    function invalidateBC(InvalidateBCRequest $request)
    {
        User::find(request('id'))->profile->invalidateBC();
        //TODO: notify to user
        Log::info('profile BC invalidated.user #' . request('id'));
        return Responder::success('تصویر شناسنامه رد شد');
    }
}
