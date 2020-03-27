<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UserLimit\AddUserLimitRequest;
use App\Http\Requests\UserLimit\EditUserLimitRequest;
use App\Http\Requests\UserLimit\RemoveUserLimitRequest;
use App\Http\Requests\UserLimit\SetUserLimitAsDefaultRequest;
use App\Http\Requests\UserLimit\ShowUserLimitRequest;
use App\Models\UserLimit;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserLimitController extends BaseController
{
    function __construct()
    {
        $this->middleware('permission:List User Limits|Add User Limits|Edit User Limits|Remove User Limits', ['only' => ['index','show']]);
        $this->middleware('permission:Add User Limits', ['only' => ['add']]);
        $this->middleware('permission:Edit User Limits', ['only' => ['edit','setAsDefault']]);
        $this->middleware('permission:Remove User Limits', ['only' => ['remove']]);
    }

    function index()
    {
        $UserLimits = UserLimit::paginate();
        return Responder::result(['pagination' => $UserLimits]);
    }

    function show(ShowUserLimitRequest $request)
    {
        $item = UserLimit::find(request('id'));
        return Responder::result(['item' => $item]);
    }

    function add(AddUserLimitRequest $request)
    {
        UserLimit::create([
            'name' => $request->input('name'),
            'max_machines' => $request->input('max_machines'),
            'max_snapshots' => $request->input('max_snapshots'),
            'max_volumes_usage' => $request->input('max_volumes_usage'),
            'default' => false
        ]);
        Log::info('new UserLimit created. user #' . Auth::id());
        return Responder::success("محدودیت کاربری با موفقیت اضافه شد");
    }

    function edit(EditUserLimitRequest $request)
    {
        UserLimit::find(\request('id'))->update([
            'name' => $request->input('name'),
            'max_machines' => $request->input('max_machines'),
            'max_snapshots' => $request->input('max_snapshots'),
            'max_volumes_usage' => $request->input('max_volumes_usage'),
        ]);
        Log::info('UserLimit edited. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("محدودیت کاربری با موفقیت ویرایش شد");
    }

    function setAsDefault(SetUserLimitAsDefaultRequest $request)
    {
        $item = UserLimit::find(request('id'));
        $item->setAsDefault();
        Log::info('UserLimit set as default. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("تغییر گروه پیش فرض موفقیت آمیز بود");
    }

    function remove(RemoveUserLimitRequest $request)
    {
        UserLimit::destroy(\request('id'));
        Log::info('UserLimit removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("محدودیت کاربری با موفقیت حذف شد");
    }
}
