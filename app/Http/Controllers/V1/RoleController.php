<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Role\AddRoleRequest;
use App\Http\Requests\Role\EditRoleRequest;
use App\Http\Requests\Role\RemoveRoleRequest;
use App\Http\Requests\Role\ShowRoleRequest;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    function __construct()
    {
        $this->middleware('permission:List Roles|Add Roles|Edit Roles|Remove Roles', ['only' => ['index','show']]);
        $this->middleware('permission:Add Roles', ['only' => ['add']]);
        $this->middleware('permission:Edit Roles', ['only' => ['edit']]);
        $this->middleware('permission:Remove Roles', ['only' => ['remove']]);
    }

    function index()
    {
        $roles = Role::paginate();
        return Responder::result(['pagination' => $roles]);
    }

    function show(ShowRoleRequest $request)
    {
        $item = Role::where('id', request('id'))->with(['permissions'])->first();
        return Responder::result(['item' => $item]);
    }

    function add(AddRoleRequest $request)
    {
        $super_admin = Role::create(['name' =>$request->input('name') , 'guard_name'=>'web']);

        foreach ($request->input('permissions') as $permission){
            $super_admin->givePermissionTo($permission);
        }

        Log::info('new Role created. user #' . Auth::id());
        return Responder::success("نقش کاربری جدید با موفقیت اضافه شد");
    }

    function edit(EditRoleRequest $request)
    {
        $role = Role::find(\request('id'));
        $role->permissions()->sync([]);
        foreach (request('permissions') as $permission){
            $role->givePermissionTo($permission);
        }
        $role->name =  $request->input('name');
        $role->save();

        Log::info('Role edited. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("نقش کاربری با موفقیت ویرایش شد");
    }

    function remove(RemoveRoleRequest $request)
    {
        Role::destroy(\request('id'));
        Log::info('Role removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("نقش کاربری با موفقیت حذف شد");
    }
}
