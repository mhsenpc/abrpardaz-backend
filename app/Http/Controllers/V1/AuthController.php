<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Models\Profile;
use App\Notifications\RegisterUserNotification;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    /**
     * @var UserRepository
     */
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

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
        $user = $this->repository->newUser(
            request('email'),
            Hash::make(request('password'))
        );

        $token =  uniqid();
        Cache::put('verification_for_' . request('email') , $token ,7 * 24 * 60 * 60);

        $user->notify(new RegisterUserNotification($token));

        return responder()->success(['message' => 'لینک فعال سازی به ایمیل شما ارسال گردید']);
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
        if (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'is_active' => true])) {
            $user = Auth::user();
            $result = [];
            $token = $user->createToken('Abrpardaz');
            $result['access_token'] = $token->accessToken;
            $result['token_type'] = 'Bearer';
            $result['expires_at'] = $token->token->expires_at;

            return responder()->success($result);
        } else {
            return responder()->error(422, 'نام کاربری یا رمز عبور صحیح نمی باشد');
        }
    }

    function forgetPassword(ForgetPasswordRequest $request)
    {

    }

    function verify(VerifyRequest $request)
    {
        $token = Cache::get('verification_for_' . request('email') );
        if(request('token') == $token){
            $this->repository->activateUserByEmail(request('email'));
            return responder()->success(['message' => 'حساب شما با موفقیت تایید شد']);
        }
        else{
            return responder()->error(422,'تایید ایمیل وارد شده امکانپذیر نمی باشد. لطفا محددا اقدام کنید');
        }
    }

    function logout(LogoutRequest $request)
    {
        Auth::logout();
        return responder()->success(['message' => 'شما با موفقیت خارج شدید']);
    }
}
