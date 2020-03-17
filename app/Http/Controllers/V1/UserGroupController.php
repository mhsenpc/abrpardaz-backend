<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UserGroup\AddUserGroupRequest;
use App\Http\Requests\UserGroup\EditUserGroupRequest;
use App\Http\Requests\UserGroup\RemoveUserGroupRequest;
use App\Http\Requests\UserGroup\SetUserGroupAsDefaultRequest;
use App\Http\Requests\UserGroup\ShowUserGroupRequest;
use App\Models\UserGroup;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserGroupController extends BaseController
{
    function index()
    {
        $UserGroups = UserGroup::paginate();
        return Responder::result(['pagination' => $UserGroups]);
    }

    function show(ShowUserGroupRequest $request)
    {
        $item = UserGroup::find(request('id'));
        return Responder::result(['item' => $item]);
    }

    function add(AddUserGroupRequest $request)
    {
        UserGroup::create([
            'name' => $request->input('name'),
            'max_machines' => $request->input('max_machines'),
            'max_snapshots' => $request->input('max_snapshots'),
            'max_volumes_usage' => $request->input('max_volumes_usage'),
            'default' => false
        ]);
        Log::info('new UserGroup created. user #' . Auth::id());
        return Responder::success("گروه کاربری با موفقیت اضافه شد");
    }

    function edit(EditUserGroupRequest $request)
    {
        UserGroup::find(\request('id'))->update([
            'name' => $request->input('name'),
            'max_machines' => $request->input('max_machines'),
            'max_snapshots' => $request->input('max_snapshots'),
            'max_volumes_usage' => $request->input('max_volumes_usage'),
        ]);
        Log::info('UserGroup edited. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("گروه کاربری با موفقیت ویرایش شد");
    }

    function setAsDefault(SetUserGroupAsDefaultRequest $request)
    {
        $item = UserGroup::find(request('id'));
        $item->setAsDefault();
        Log::info('UserGroup set as default. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("تغییر گروه پیش فرض موفقیت آمیز بود");
    }

    function remove(RemoveUserGroupRequest $request)
    {
        UserGroup::destroy(\request('id'));
        Log::info('UserGroup removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("گروه کاربری با موفقیت حذف شد");
    }
}
