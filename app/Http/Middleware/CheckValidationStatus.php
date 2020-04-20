<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckValidationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $profile = $user->profile;
        if ($user->hasRole('Normal User')) {
            if (empty($profile->first_name) || empty($profile->last_name)) {
                return response(['message' => 'اطلاعات کاربری شما ناقص است.', 'code' => 'basic_info'], 403);
            }

            if (empty($profile->national_code)) {
                return response(['message' => 'بدون وارد کردن کد ملی قادر به استفاده از سرویس نمی باشید', 'code' => 'national_code'], 403);
            }

            if (empty($profile->postal_code)) {
                return response(['message' => 'بدون وارد کردن کد پستی قادر به استفاده از سرویس نمی باشید', 'code' => 'postal_code'], 403);
            }

            if (empty($profile->mobile_verified_at)) {
                return response(['message' => 'بدون فعال کردن شماره تلفن همراه خود قادر به استفاده از سرویس نمی باشید', 'code' => 'mobile_validation'], 403);
            }

            if ($profile->national_card_front_status != 2 || $profile->national_card_back_status != 2 || $profile->birth_certificate_status != 2) {
                return response(['message' => 'تا زمانیکه مدارک خود را تکمیل نکرده باشید امکان استفاده از این سرویس را ندارید', 'code' => 'certificates'], 403);
            }

            if ($profile->validation_status == 3) {
                return response(['message' => 'اطلاعات وارد شده در حساب کاربری پذیرفته نشده است. دلیل: '. $profile->validation_reason , 'code' => 'certificates'], 403);
            }
        }

        $response = $next($request);
        return $response;
    }
}
