<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Models\Profile;
use App\Notifications\RegisterUserNotification;
use App\Notifications\ResetPasswordNotification;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/register",
     *      summary="Register a new user",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
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
    function register(RegisterRequest $request)
    {
        $user = User::newUser(
            request('email'),
            Hash::make(request('password')),
        );

        $token = uniqid();
        Cache::put('verification_for_' . request('email'), $token, 7 * 24 * 60 * 60);

        $user->notify(new RegisterUserNotification(request('email'), $token));
        Log::info('register user . sent token to email '.request('email'));

        return Responder::success('لینک فعال سازی به ایمیل شما ارسال گردید');
    }

    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/login",
     *      summary="Login a new user",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
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
    function login(LoginRequest $request)
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')/*, 'is_active' => true*/])) {
            $user = Auth::user();
            $result = [];
            $token = $user->createToken('Abrpardaz');
            $result['access_token'] = $token->accessToken;
            $result['token_type'] = 'Bearer';
            $result['expires_at'] = $token->token->expires_at;
            $result['message'] = 'شما با موفقیت وارد شدید';
            $result['user_id'] = Auth::id();

            Log::info('user logged in '.request('email'));

            return Responder::result($result);
        } else {
            return Responder::error('نام کاربری یا رمز عبور صحیح نمی باشد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/forgetPassword",
     *      summary="Send a password reset link to the email",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
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
    function forgetPassword(ForgetPasswordRequest $request)
    {
        $token = uniqid();
        Cache::put('forget_token_for_' . request('email'), $token, 7 * 24 * 60 * 60);

        Auth::user()->notify(new ResetPasswordNotification(request('email'), $token));

        Log::info('user forgot his password '.request('email'));
        return Responder::success('لینک بازنشانی رمز به ایمیل شما ارسال گردید');
    }

    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/resetPassword",
     *      summary="Changes the password using the provided token",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password_confirmation",
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
    function resetPassword(ResetPasswordRequest $request)
    {
        $token = Cache::get('forget_token_for_' . request('email'));
        if (request('token') == $token) {
            User::updatePassword(
                request('email'),
                Hash::make(request('password'))
            );

            Cache::forget('forget_token_for_' . request('email'));
            Log::info('reset password successful for '.request('email'));
            return Responder::success('بازنشانی رمز عبور با موفقیت انجام شد');
        } else {
            Log::warning('invalid reset password token for '.request('email').' token: '.request('token'));
            return Responder::error('بازنشانی رمز با توکن وارد شده امکان پذیر نمی باشد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/changePassword",
     *      summary="Changes the user password",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="current_password",
     *         in="query",
     *         description="The current password of the user",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="new_password",
     *         in="query",
     *         description="The new password you want to set for the user",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="new_password_confirmation",
     *         in="query",
     *         description="Confirmation of the new password",
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
    function changePassword(ChangePasswordRequest $request){
        $user = Auth::user();
        if (Hash::check(request('current_password'), $user->password)) {
            User::updatePassword(
                $user->email,
                Hash::make(request('new_password'))
            );

            Log::info('change password successful for '.$user->email);
            return Responder::success('رمز عبور شما با موفقیت تغییر یافت');
        } else {
            Log::warning('change password failed for '.$user->email);
            return Responder::error('رمز عبور قبلی شما صحیح نمی باشد');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Authentication"},
     *      path="/auth/verify",
     *      summary="Verify the user's email",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="The email which user has been registered with",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="The token which has been sent to the user's email",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *
     *     )
     *
     */
    function verify(VerifyRequest $request)
    {
        $token = Cache::get('verification_for_' . request('email'));
        if (request('token') == $token) {
            User::activateUserByEmail(request('email'));
            Cache::forget('verification_for_' . request('email'));

            Log::info('user verification successful for '.request('email'));
            return Responder::success('حساب کاربری شما با موفقیت تایید شد');
        } else {
            Log::warning('user verification failed for '.request('email').' with token '.request('token'));
            return Responder::error('تایید ایمیل وارد شده امکانپذیر نمی باشد. لطفا محددا اقدام کنید');
        }
    }

    /**
     * @OA\Put(
     *      tags={"Authentication"},
     *      path="/auth/logout",
     *      summary="Removes your token",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     )
     *
     */
    function logout(LogoutRequest $request)
    {
        Auth::logout();
        return Responder::success('شما با موفقیت خارج شدید');
    }
}
