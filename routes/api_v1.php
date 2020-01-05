<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->namespace('V1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('forgetPassword', 'AuthController@forgetPassword');
        Route::any('verify', 'AuthController@verify')->name('auth.verify');
        Route::post('logout', 'AuthController@logout');
    });

    Route::middleware([/*'auth:api'*/])->group(function () {
        Route::prefix('images')->group(function () {
            Route::get('list', 'ImageController@os');
        });

        Route::prefix('machines')->group(function () {
            Route::get('list', 'MachineController@index');
            Route::post('createFromImage', 'MachineController@createFromImage');
            Route::post('createFromSnapshot', 'MachineController@createFromSnapshot');
        });

        Route::prefix('machines/{id}')->group(function () {
            Route::post('console', 'MachineController@console');
            Route::post('powerOn', 'MachineController@powerOn');
            Route::post('powerOff', 'MachineController@powerOff');
            Route::post('takeSnapshot', 'MachineController@takeSnapshot');
            Route::post('resendInfo', 'MachineController@resendInfo');
            Route::put('rename', 'MachineController@rename');
            Route::delete('remove', 'MachineController@remove');
        });

/*        Route::prefix('payment')->group(function () {
            Route::post('requestPayment', 'PaymentController@requestPayment');
            Route::post('result', 'PaymentController@paymentResult');
        });*/

        Route::prefix('plans')->group(function () {
            Route::get('list', 'PlanController@index');
        });

        Route::prefix('profile')->group(function () {
            Route::get('getUserInfo', 'ProfileController@getUserInfo');
            Route::post('setUserInfo', 'ProfileController@setUserInfo');
            Route::post('requestSetMobile', 'ProfileController@requestSetMobile');
            Route::post('setMobile', 'ProfileController@setMobile');
        });

        Route::prefix('snapshots')->group(function () {
            Route::get('list', 'SnapshotController@index');
            Route::get('ofMachine', 'SnapshotController@ofMachine');

            Route::prefix('{id}')->group(function () {
                Route::get('getProgress', 'SnapshotController@getProgress');
                Route::put('rename', 'SnapshotController@rename');
                Route::delete('remove', 'SnapshotController@remove');
            });
        });

        Route::prefix('sshKeys')->group(function () {
            Route::get('list', 'SSHKeyController@index');
            Route::post('add', 'SSHKeyController@add');
            Route::put('edit', 'SSHKeyController@edit');
            Route::delete('remove', 'SSHKeyController@remove');
        });
    });
});
