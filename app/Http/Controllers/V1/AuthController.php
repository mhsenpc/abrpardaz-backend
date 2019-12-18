<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    function register(RegisterRequest $request){
        return 'register';
    /*    return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);*/
    }

    function login(LoginRequest $request){

    }

    function forgetPassword(ForgetPasswordRequest $request){

    }

    function verify(VerifyRequest $request){

    }

    function logout(LogoutRequest $request){

    }
}
