<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SSHKey\AddKeyRequest;
use App\Http\Requests\SSHKey\EditKeyRequest;
use App\Repositories\SSHKeyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSHKeyController extends BaseController
{
    /**
     * @var SSHKeyRepository
     */
    protected $repository;

    public function __construct(SSHKeyRepository $repository)
    {
        $this->repository = $repository;
    }

    function index()
    {
        return responder()->success(['list' => $this->repository->all()]);
    }

    function add(AddKeyRequest $request)
    {
        $this->repository->create([
            'name'=> $request->input('name'),
            'content' => $request->input('content'),
            'user_id' => Auth::id()
        ]);
        return responder()->success(['message'=>"کلید با موفقیت اضافه شد"]);
    }

    function edit(EditKeyRequest $request)
    {
        $this->repository->find($request->input('id'))->update([
            'name'=> $request->input('name'),
            'content' => $request->input('content')
        ]);
        return responder()->success(['message'=>"کلید با موفقیت ویرایش شد"]);
    }

    function remove(Request $request)
    {
        $this->repository->delete(\request('id'));
        return responder()->success(['message'=>"کلید با موفقیت حذف شد"]);
    }
}
