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

class PermissionsController extends BaseController
{
    function index()
    {
        $permissions = Permission::all();
        return Responder::result(['list' => $permissions]);
    }
}
