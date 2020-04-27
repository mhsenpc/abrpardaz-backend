<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Project\AddRequest;
use App\Http\Requests\Project\AddMemberRequest;
use App\Http\Requests\Project\LeaveRequest;
use App\Http\Requests\Project\RemoveRequest;
use App\Http\Requests\Project\RemoveMemberRequest;
use App\Http\Requests\Project\RenameRequest;
use App\Models\Project;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Project"},
     *      path="/projects/list",
     *      summary="Get list of your projects",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *
     *     )
     *
     */
    function index(){
        return Responder::result(['list' => Project::all() ]);
    }

    /**
     * @OA\Post(
     *      tags={"Project"},
     *      path="/projects/add",
     *      summary="Create a new project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function add(AddRequest $request){
        $project = Project::create([
            'owner_id' => Auth::id(),
            'name' => request('name')
        ]);
        Auth::user()->projects()->attach($project->id);

        Log::info('new project added.user #'.Auth::id());
        return Responder::success('پروژه با موفقیت ایجاد شد');
    }

    /**
     * @OA\post(
     *      tags={"Project"},
     *      path="/projects/{id}/rename",
     *      summary="Rename the project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function rename(RenameRequest $request){
        $project = Project::find(request('id'));
        $project->name = request('name');
        $project->save();
        Log::info('project renamed.user #'.Auth::id());
        return Responder::success('نام پروژه با موفقیت تغییر یافت');
    }

    /**
     * @OA\post(
     *      tags={"Project"},
     *      path="/projects/{id}/addMember",
     *      summary="Add a member to the project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    function addMember(AddMemberRequest $request){
        $user = User::find(request('user_id'));
        $project = Project::find(request('id'));
        if($project->owner_id != Auth::id())
            return Responder::error('شما اجازه تغییر این پروزه را ندارید');

        $user->project()->syncWithoutDetaching($project->id);

        Log::info('member added to the project. project #'.request('id').',member #'.request('user_id').'.user #'.Auth::id());
        return Responder::success('کاربر با موفقیت به پروژه اضافه شد');
    }

    /**
     * @OA\post(
     *      tags={"Project"},
     *      path="/projects/{id}/removeMember",
     *      summary="Remove a member from the project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    function removeMember(RemoveMemberRequest $request){
        $user = User::find(request('user_id'));
        $project = Project::find(request('id'));
        if($project->owner_id != Auth::id())
            return Responder::error('شما اجازه تغییر این پروزه را ندارید');

        $user->project()->detach($project->id);

        Log::info('member removed from the project. project #'.request('id').',member #'.request('user_id').'.user #'.Auth::id());
        return Responder::success('کاربر با موفقیت از پروژه حذف گردید');
    }

    /**
     * @OA\Put(
     *      tags={"Project"},
     *      path="/projects/{id}/leave",
     *      summary="Removes the current user from the project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function leave(LeaveRequest $request){
        $project = Project::find(request('id'));

        Auth::user()->projects()->detach($project->id);

        Log::info('Leave project #'.request('id').',user #'.Auth::id());
        return Responder::success('کاربر با موفقیت از پروژه حذف گردید');
    }

    /**
     * @OA\Delete(
     *      tags={"Project"},
     *      path="/projects/{id}/remove",
     *      summary="Removes the project",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function remove(RemoveRequest $request){
        $project = Project::findOrFail(request('id'));
        if($project->owner_id != Auth::id())
            return Responder::error('شما اجازه حذف این پروزه را ندارید');

        if($project->machines->count() >0)
            return Responder::error('فقط پروزه های خالی می توانند حذف شوند');

        $project->delete();
        Log::info('remove project #'.request('id').',user #'.Auth::id());
        return Responder::success('پروژه با موفقیت حذف شد');
    }
}
