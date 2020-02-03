<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Notification\DeleteRequest;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Notification"},
     *      path="/notifications/list",
     *      summary="List of the notifications",
     *      description="",
     *
     *
     * @OA\Response(
     *         response="default",
     *         description="List of the notifications"
     *     ),
     *     )
     *
     */
    function index(){
        $unread = Auth::user()->unreadNotifications;
        return Responder::result([
            'list'=> Auth::user()->notifications,
            'unread_count' =>$unread->count()
        ]);
    }

    /**
     * @OA\Post(
     *      tags={"Notification"},
     *      path="/notifications/markAllRead",
     *      summary="mark all notifications as read",
     *      description="",
     *
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function markAllRead(){
        Auth::user()->unreadNotifications->markAsRead();
        return Responder::success('اطلاعیه با موفقیت به عنوان خوانده شده علامت گذاری شدند');
    }

    /**
     * @OA\Delete(
     *      tags={"Notification"},
     *      path="/notifications/{id}/delete",
     *      summary="delete a notifications",
     *      description="",
     *
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
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
    function delete(DeleteRequest $request){
        Auth::user()->notifications()
            ->where('id', request('id'))
            ->get()
            ->first()
            ->delete();
        return Responder::success('اطلاعیه با موفقیت حذف گردید');
    }
}
