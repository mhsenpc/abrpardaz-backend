<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register','V1\\AuthController@register');
        Route::post('login','V1\\AuthController@login');
        Route::post('forgetPassword','V1\\AuthController@forgetPassword');
        Route::any('verify','V1\\AuthController@verify')->name('auth.verify');
        Route::post('logout','V1\\AuthController@logout');
    });

    Route::middleware(['verified' /*'auth:api'*/])->group(function () {
        Route::prefix('images')->group(function () {
            Route::post('os','V1\\ImageController@os');
        });

        Route::prefix('machines')->group(function () {
            Route::get('list','V1\\MachineController@index');
            Route::post('createFromImage','V1\\MachineController@createFromImage');
            Route::post('createFromSnapshot','V1\\MachineController@createFromSnapshot');
            Route::post('console','V1\\MachineController@console');
            Route::post('powerOn','V1\\MachineController@powerOn');
            Route::post('powerOff','V1\\MachineController@powerOff');
            Route::post('takeSnapshot','V1\\MachineController@takeSnapshot');
            Route::post('resendInfo','V1\\MachineController@resendInfo');
            Route::post('rename','V1\\MachineController@rename');
            Route::delete('remove','V1\\MachineController@remove');
        });

        Route::prefix('payment')->group(function () {
            Route::post('requestPayment','V1\\PaymentController@requestPayment');
            Route::post('result','V1\\PaymentController@paymentResult');
        });

        Route::prefix('plans')->group(function () {
            Route::get('list','V1\\PlanController@index');
        });

        Route::prefix('profile')->group(function () {
            Route::get('getUserInfo','V1\\ProfileController@getUserInfo');
            Route::post('setUserInfo','V1\\ProfileController@setUserInfo');
            Route::post('requestSetMobile','V1\\ProfileController@requestSetMobile');
            Route::post('setMobile','V1\\ProfileController@setMobile');
        });

        Route::prefix('snapshots')->group(function () {
            Route::get('list','V1\\SnapshotController@index');
            Route::post('getProgress','V1\\SnapshotController@getProgress');
            Route::post('rename','V1\\SnapshotController@rename');
            Route::delete('remove','V1\\SnapshotController@remove');
        });

        Route::prefix('ssh_keys')->group(function () {
            Route::get('list','V1\\SSHKeyController@index');
            Route::post('add','V1\\SSHKeyController@add');
            Route::post('edit','V1\\SSHKeyController@edit');
            Route::delete('remove','V1\\SSHKeyController@remove');
        });
    });
});
