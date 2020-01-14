<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->namespace('V1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('forgetPassword', 'AuthController@forgetPassword');
        Route::post('resetPassword', 'AuthController@resetPassword');
        Route::any('verify', 'AuthController@verify')->name('auth.verify');
        Route::post('logout', 'AuthController@logout');
    });

    Route::middleware([/*'auth:api'*/])->group(function () {
        Route::prefix('images')->group(function () {
            Route::get('os', 'ImageController@os');
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
            Route::put('resendInfo', 'MachineController@resendInfo');
            Route::post('rename', 'MachineController@rename');
            Route::delete('remove', 'MachineController@remove');
        });

        Route::prefix('plans')->group(function () {
            Route::get('list', 'PlanController@index');
        });

        Route::prefix('profile')->group(function () {
            Route::get('getUserInfo', 'ProfileController@getUserInfo');
            Route::post('setUserBasicInfo', 'ProfileController@setUserBasicInfo');
            Route::post('requestSetMobile', 'ProfileController@requestSetMobile');
            Route::post('setMobile', 'ProfileController@setMobile');
            Route::post('requestSetPhone', 'ProfileController@requestSetPhone');
            Route::post('setPhone', 'ProfileController@setPhone');
            Route::post('uploadNationalCardFront', 'ProfileController@uploadNationalCardFront');
            Route::post('uploadNationalCardBack', 'ProfileController@uploadNationalCardBack');
            Route::post('uploadBirthCertificate', 'ProfileController@uploadBirthCertificate');
        });

        Route::prefix('snapshots')->group(function () {
            Route::get('list', 'SnapshotController@index');
            Route::get('ofMachine', 'SnapshotController@ofMachine');

            Route::prefix('{id}')->group(function () {
                Route::get('getProgress', 'SnapshotController@getProgress');
                Route::post('rename', 'SnapshotController@rename');
                Route::delete('remove', 'SnapshotController@remove');
            });
        });

        Route::prefix('sshKeys')->group(function () {
            Route::get('list', 'SSHKeyController@index');
            Route::post('add', 'SSHKeyController@add');
            Route::post('edit', 'SSHKeyController@edit');
            Route::delete('remove', 'SSHKeyController@remove');
        });

        Route::prefix('tickets')->group(function () {
            Route::get('list', 'TicketController@index');
            Route::get('categories', 'TicketController@categories');
            Route::post('newTicket', 'TicketController@newTicket');

            Route::prefix('{id}')->group(function () {
                Route::post('newReply', 'TicketController@newReply');
                Route::put('close', 'TicketController@close');
                Route::get('show', 'TicketController@show');
            });
        });

        Route::prefix('volumes')->group(function () {
            Route::get('list', 'VolumeController@index');
            Route::post('createVolume', 'VolumeController@createVolume');

            Route::prefix('{id}')->group(function () {
                Route::post('attachToMachine', 'VolumeController@attachToMachine');
                Route::post('detachFromMachine', 'VolumeController@detachFromMachine');
                Route::post('rename', 'VolumeController@rename');
                Route::delete('remove', 'VolumeController@remove');
            });
        });

    });
});
