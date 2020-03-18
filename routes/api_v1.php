<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->namespace('V1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('forgetPassword', 'AuthController@forgetPassword');
        Route::post('resetPassword', 'AuthController@resetPassword');
        Route::post('changePassword', 'AuthController@changePassword');
        Route::any('verify', 'AuthController@verify');
        Route::put('logout', 'AuthController@logout');
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('images')->group(function () {
            Route::get('os', 'ImageController@os');
        });

        Route::prefix('machines')->group(function () {
            Route::get('list', 'MachineController@index');
            Route::get('ofProject/{id}', 'MachineController@ofProject');
            Route::post('create', 'MachineController@create');
        });

        Route::prefix('machines/{id}')->group(function () {
            Route::post('console', 'MachineController@console');
            Route::get('details', 'MachineController@details');
            Route::get('activities', 'MachineController@activities');
            Route::put('powerOn', 'MachineController@powerOn');
            Route::put('powerOff', 'MachineController@powerOff');
            Route::put('enableBackup', 'MachineController@enableBackup');
            Route::put('disableBackup', 'MachineController@disableBackup');
            Route::put('resendInfo', 'MachineController@resendInfo');
            Route::post('rescale', 'MachineController@rescale');
            Route::post('rebuild', 'MachineController@rebuild');
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
            Route::prefix('{id}')->group(function () {
                Route::put('validate', 'ProfileController@validateProfile');
                Route::put('invalidate', 'ProfileController@invalidateProfile');
                Route::put('validateNCFront', 'ProfileController@validateNCFront');
                Route::put('invalidateNCFront', 'ProfileController@invalidateNCFront');
                Route::put('validateNCBack', 'ProfileController@validateNCBack');
                Route::put('invalidateNCBack', 'ProfileController@invalidateNCBack');
                Route::put('validateBC', 'ProfileController@validateBC');
                Route::put('invalidateBC', 'ProfileController@invalidateBC');
            });
        });

        Route::prefix('snapshots')->group(function () {
            Route::get('list', 'SnapshotController@index');
            Route::get('ofMachine', 'SnapshotController@ofMachine');
            Route::post('takeSnapshot', 'SnapshotController@takeSnapshot');

            Route::prefix('{id}')->group(function () {
                Route::get('getProgress', 'SnapshotController@getProgress');
                Route::post('rename', 'SnapshotController@rename');
                Route::delete('remove', 'SnapshotController@remove');
            });
        });

        Route::prefix('sshKeys')->group(function () {
            Route::get('list', 'SSHKeyController@index');
            Route::post('add', 'SSHKeyController@add');

            Route::prefix('{id}')->group(function () {
                Route::get('show', 'SSHKeyController@show');
                Route::post('edit', 'SSHKeyController@edit');
                Route::delete('remove', 'SSHKeyController@remove');
            });
        });

        Route::prefix('projects')->group(function () {
            Route::get('list', 'ProjectController@index');
            Route::post('add', 'ProjectController@add');

            Route::prefix('{id}')->group(function () {
                Route::post('rename', 'ProjectController@rename');
                Route::post('addMember', 'ProjectController@addMember');
                Route::post('removeMember', 'ProjectController@removeMember');
                Route::put('leave', 'ProjectController@leave');
                Route::delete('remove', 'ProjectController@remove');
            });
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

        Route::prefix('notifications')->group(function () {
            Route::get('list', 'NotificationController@index');
            Route::post('markAllRead', 'NotificationController@markAllRead');
            Route::prefix('{id}')->group(function () {
                Route::delete('delete', 'NotificationController@delete');
            });
        });

        Route::prefix('invoices')->group(function () {
            Route::get('list', 'InvoiceController@index');
        });

        Route::prefix('transactions')->group(function () {
            Route::get('list', 'TransactionsController@index');
        });

        Route::prefix('limits')->group(function () {
            Route::get('list', 'LimitController@index');
        });

        Route::prefix('users')->group(function () {
            Route::get('list', 'UserController@index');
            Route::post('add', 'UserController@add');

            Route::prefix('{id}')->group(function () {
                Route::get('show', 'UserController@show');
                Route::post('edit', 'UserController@edit');
                Route::post('changeUserGroup', 'UserController@changeUserGroup');
                Route::put('suspend', 'UserController@suspend');
                Route::put('unsuspend', 'UserController@unsuspend');
                Route::put('verifyEmail', 'UserController@verifyEmail');
                Route::delete('remove', 'UserController@remove');
            });
        });

        Route::prefix('images')->group(function () {
            Route::get('list', 'ImageController@index');
            Route::post('add', 'ImageController@add');

            Route::prefix('{id}')->group(function () {
                Route::get('show', 'ImageController@show');
                Route::post('edit', 'ImageController@edit');
                Route::delete('remove', 'ImageController@remove');
            });
        });

        Route::prefix('plans')->group(function () {
            Route::get('list', 'PlanController@index');
            Route::post('add', 'PlanController@add');

            Route::prefix('{id}')->group(function () {
                Route::get('show', 'PlanController@show');
                Route::post('edit', 'PlanController@edit');
                Route::delete('remove', 'PlanController@remove');
            });
        });

        Route::prefix('user_groups')->group(function () {
            Route::get('list', 'UserGroupController@index');
            Route::post('add', 'UserGroupController@add');

            Route::prefix('{id}')->group(function () {
                Route::get('show', 'UserGroupController@show');
                Route::post('edit', 'UserGroupController@edit');
                Route::put('setAsDefault', 'UserGroupController@setAsDefault');
                Route::delete('remove', 'UserGroupController@remove');
            });
        });
    });
});
