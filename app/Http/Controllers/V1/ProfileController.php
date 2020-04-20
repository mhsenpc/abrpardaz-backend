<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\profile\InvalidateBCRequest;
use App\Http\Requests\profile\InvalidateNCBackRequest;
use App\Http\Requests\profile\InvalidateNCFrontRequest;
use App\Http\Requests\profile\InvalidateNCNumberRequest;
use App\Http\Requests\profile\InvalidateProfileRequest;
use App\Http\Requests\Profile\RequestSetMobileRequest;
use App\Http\Requests\Profile\RequestSetPhoneRequest;
use App\Http\Requests\Profile\SetMobileRequest;
use App\Http\Requests\Profile\SetPhoneRequest;
use App\Http\Requests\Profile\SetUserAddressRequest;
use App\Http\Requests\Profile\SetUserInfoRequest;
use App\Http\Requests\profile\UploadBirthCertificateRequest;
use App\Http\Requests\profile\UploadNationalCardBackRequest;
use App\Http\Requests\profile\UploadNationalCardFrontRequest;
use App\Http\Requests\profile\ValidateBCRequest;
use App\Http\Requests\profile\ValidateNCBackRequest;
use App\Http\Requests\profile\ValidateNCFrontRequest;
use App\Http\Requests\profile\ValidateNCNumberRequest;
use App\Http\Requests\profile\ValidateProfileRequest;
use App\Models\Profile;
use App\Notifications\BCInvalidatedNotification;
use App\Notifications\BCValidatedNotification;
use App\Notifications\NCBackInvalidatedNotification;
use App\Notifications\NCBackValidatedNotification;
use App\Notifications\NCFrontInvalidatedNotification;
use App\Notifications\NCFrontValidatedNotification;
use App\Notifications\NCInvalidatedNotification;
use App\Notifications\NCValidatedNotification;
use App\Notifications\ProfileInvalidatedNotification;
use App\Notifications\ProfileValidatedNotification;
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
    function __construct()
    {
        $this->middleware('permission:Validate Profile', ['only' => ['validateProfile']]);
        $this->middleware('permission:Invalidate Profile', ['only' => ['invalidateProfile']]);
        $this->middleware('permission:Validate Documents', ['only' => ['validateNCFront', 'invalidateNCFront', 'validateNCBack', 'invalidateNCBack', 'validateBC', 'invalidateBC']]);
    }

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

        if (!empty($user->profile->national_card_front))
            $user->profile->national_card_front = $path = asset('storage/' . $user->profile->national_card_front);

        if (!empty($user->profile->national_card_back))
            $user->profile->national_card_back = $path = asset('storage/' . $user->profile->national_card_back);

        if (!empty($user->profile->birth_certificate))
            $user->profile->birth_certificate = $path = asset('storage/' . $user->profile->birth_certificate);
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
     *         name="organization_name",
     *         in="query",
     *         description="",
     *         required=false,
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
        $values = [
            'national_code' => \request('national_code'),
            'first_name' => \request('first_name'),
            'last_name' => \request('last_name'),
        ];

        if(!empty(request('organization_name'))){
            $values ['organization'] = true;
            $values ['organization_name'] = request('organization_name');
        }
        else{
            $values ['organization'] = false;
            $values ['organization_name'] = null;
        }

        Profile::where('id', Auth::user()->profile_id)->update($values);

        Log::info('set user basic info user #' . Auth::id());
        return Responder::success('اطلاعات شما با موفقیت ذخیره شد');
    }

    /**
     * @OA\Post(
     *      tags={"Profile"},
     *      path="/profile/setUserAddress",
     *      summary="Set user Address",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
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
    function setUserAddress(SetUserAddressRequest $request)
    {
        Profile::where('id', Auth::user()->profile_id)->update([
            'address' => \request('address'),
            'postal_code' => \request('postal_code'),
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
        $tries_key = 'sms_count_' . request('mobile');
        if (Cache::get($tries_key, 0) > 3)
            return Responder::error('لطفا ساعاتی دیگر جهت ارسال پیامک تلاش کنید');

        if (config('app.env') == 'local') {
            $code = 11111;
            $result = true;
        } else {
            $code = rand(11111, 99999);
            $result = MobileService::sendActivationCode(request('mobile'), $code);
        }

        if ($result) {
            Cache::put('validation_code_for_' . request('mobile'), $code, 5 * 60);
            Cache::put($tries_key, Cache::get($tries_key, 0) + 1, 60 * 60);
            Log::info('request set mobile .mobile #' . request('mobile') . ' ,user #' . Auth::id());
            return Responder::success('کد فعال سازی به شماره موبایل شما ارسال گردید');
        } else {
            Log::warning('failed to send sms to mobile #' . request('mobile') . ' ,user #' . Auth::id());
            return Responder::error('متاسفانه ارسال پیامک با شکست مواجه شد');
        }
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
        $tries_key = 'call_count_' . request('phone');
        if (Cache::get($tries_key, 0) > 3)
            return Responder::error('لطفا ساعاتی دیگر جهت برقراری تماس تلاش کنید');

        if (config('app.env') == 'local'){
            $code = 11111;
            $result = true;
        }
        else{
            $code = rand(11111, 99999);
            $result = PhoneService::sendActivationCode(request('phone'), $code);
        }
        if ($result) {
            Cache::put('validation_code_for_' . request('phone'), $code, 5 * 60);
            Cache::put($tries_key, Cache::get($tries_key, 0) + 1, 60 * 60);
            Log::info('request set phone .phone #' . request('phone') . ' ,user #' . Auth::id());
            return Responder::success('منتظر دریافت کد فعال سازی روی این شماره باشید');
        } else {
            Log::warning('failed to call phone #' . request('phone') . ' ,user #' . Auth::id());
            return Responder::error('متاسفانه تماس با شکست مواجه شد');
        }


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
            Auth::user()->profile->setNcFront($path);
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
            Auth::user()->profile->setNCBack($path);
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
            Auth::user()->profile->setBC($path);
            Log::info('birth certificate uploaded.user #' . Auth::id());
            return Responder::success('تصویر با موفقیت بارگذاری شد');
        } else {
            Log::warning('failed to upload birth certificate .user #' . Auth::id());
            return Responder::error('در بارگذاری تصویر مشکلی وجود دارد');
        }
    }

    function validateProfile(ValidateProfileRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->validateProfile();
        $user->notify(new ProfileValidatedNotification($user, $user->profile));
        Log::info('profile validated.user #' . request('id'));
        return Responder::success('پروفایل با موفقیت تایید شد');
    }

    function invalidateProfile(InvalidateProfileRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->invalidateProfile(request('reason'));
        $user->notify(new ProfileInvalidatedNotification($user, $user->profile, request('reason')));
        Log::info('profile validated.user #' . request('id'));
        return Responder::success('پروفایل با موفقیت به حالت تایید نشده تبدیل شد');
    }

    function validateNCFront(ValidateNCFrontRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->validateNCFront();
        $user->notify(new NCFrontValidatedNotification());
        Log::info('profile NCFront validated.user #' . request('id'));
        return Responder::success('تصویر جلوی کارت ملی تایید شد');
    }

    function invalidateNCFront(InvalidateNCFrontRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->invalidateNCFront(request('reason'));
        $user->notify(new NCFrontInvalidatedNotification($user, $user->profile, request('reason')));
        Log::info('profile NCFront invalidated.user #' . request('id'));
        return Responder::success('تصویر جلوی کارت ملی رد شد');
    }

    function validateNCBack(ValidateNCBackRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->validateNCBack();
        $user->notify(new NCBackValidatedNotification());
        Log::info('profile NCBack validated.user #' . request('id'));
        return Responder::success('تصویر پشت کارت ملی تایید شد');
    }

    function invalidateNCBack(InvalidateNCBackRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->invalidateNCBack(request('reason'));
        $user->notify(new NCBackInvalidatedNotification($user, $user->profile, request('reason')));
        Log::info('profile NCBack invalidated.user #' . request('id'));
        return Responder::success('تصویر پشت کارت ملی رد شد');
    }

    function validateBC(ValidateBCRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->validateBC();
        $user->notify(new BCValidatedNotification());
        Log::info('profile BC validated.user #' . request('id'));
        return Responder::success('تصویر شناسنامه تایید شد');
    }

    function invalidateBC(InvalidateBCRequest $request)
    {
        $user = User::find(request('id'));
        $user->profile->invalidateBC(request('reason'));
        $user->notify(new BCInvalidatedNotification($user, $user->profile, request('reason')));
        Log::info('profile BC invalidated.user #' . request('id'));
        return Responder::success('تصویر شناسنامه رد شد');
    }
}
