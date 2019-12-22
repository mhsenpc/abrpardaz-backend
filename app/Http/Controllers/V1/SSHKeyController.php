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
        $this->repository->create($request->input('name'), $request->input('content'), 3);
        return responder()->success("کلید با موفقیت اضافه شد");
    }

    function edit(EditKeyRequest $request)
    {
        $this->repository->getById($request->input('id'))->update([
            'content' => $request->input('content')
        ]);
        return responder()->success("کلید با موفقیت ویرایش شد");
    }

    function remove(Request $request)
    {
        $this->repository->deleteById($request->input('id'));
        return responder()->success("کلید با موفقیت حذف شد");
    }
}
