<?php

# Special thanks to: Wael Salah
# https://webmobtuts.com/backend-development/lets-implement-a-simple-ticketing-system-with-laravel/

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Ticket\CloseTicketRequest;
use App\Http\Requests\Ticket\NewReplyRequest;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Http\Requests\Ticket\ShowTicketRequest;
use App\Models\Category;
use App\Models\Reply;
use App\Models\Ticket;
use App\Notifications\NewTicketAdminNotification;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReplyAdminNotification;
use App\Notifications\TicketReplyNotification;
use App\Notifications\TicketStatusNotification;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Ticket"},
     *      path="/tickets/list",
     *      summary="Returns the list of your tickets",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     )
     *
     */
    public function index()
    {
        /*
         "all"همه
         'awaiting_reply'انتظار پاسخ
         'flagged'علامت دار
         'active'فعال
         'open'باز
         'answered'پاسخ داده شده
         'customer_reply'پاسخ مشتری
         'on_hold'معلق
         'in_progress'در جریان
         'closed'بسته شده
        */
        $ticket_operators = User::permission('Ticket Operator')->get()->pluck('id');
        switch (request('filter')) {
            case 'awaiting_reply':
                $tickets = Ticket::has('replies', '=', 0)->paginate(5)->toArray();
                break;
            case 'open':
                $tickets = Ticket::where('status', 'Open')->paginate(5)->toArray();
                break;
            case 'answered':
                $tickets = Ticket::whereHas('latestReply')->whereIn('user_id', $ticket_operators)->paginate(5)->toArray();
                break;
            case 'customer_reply':
                $tickets = Ticket::whereHas('latestReply')->whereNotIn('user_id', $ticket_operators)->paginate(5)->toArray();
                break;
            case 'closed':
                $tickets = Ticket::where('status', 'Closed')->paginate(5)->toArray();
                break;
            case 'all':
                $tickets = Ticket::paginate(5)->toArray();
                break;
        }

        return Responder::result(['pagination' => $tickets]);
    }

    /**
     * @OA\Get(
     *      tags={"Ticket"},
     *      path="/tickets/categories",
     *      summary="Returns the list of ticket categories",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     )
     *
     */
    public function categories()
    {
        $tickets = Category::all();
        return Responder::result(['list' => $tickets->toArray()]);
    }


    /**
     * @OA\Post(
     *      tags={"Ticket"},
     *      path="/tickets/newTicket",
     *      summary="Crates a new ticket",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="machine",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="category",
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
    public function newTicket(NewTicketRequest $request)
    {
        $ticket = new Ticket([
            'title' => \request('title'),
            'user_id' => Auth::id(),
            'ticket_id' => strtoupper(Str::random(10)),
            'category_id' => \request('category'),
            'priority' => \request('priority'),
            'message' => \request('message'),
            'status' => "Open"
        ]);

        if (!empty(\request('machine'))) {
            $ticket->machine_id = \request('machine');
        }
        $ticket->save();

        //notif to user himself
        Auth::user()->notify(new NewTicketNotification($ticket, Auth::user(), Auth::user()->profile));
        //notif to admins
        $admins = User::permission('Ticket Operator')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewTicketAdminNotification($ticket, Auth::user(), Auth::user()->profile));
        }
        Log::info('new ticket created. user #' . Auth::id());
        return Responder::success('تیکت جدید با موفقیت ایجاد شد');
    }

    /**
     * @OA\Post(
     *      tags={"Ticket"},
     *      path="/tickets/{id}/newReply",
     *      summary="Insert reply for a ticket",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
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
     *     )
     *
     */
    public function newReply(NewReplyRequest $request)
    {
        $reply = Reply::create([
            'ticket_id' => \request('id'),
            'user_id' => Auth::id(),
            'comment' => \request('comment')
        ]);


        if ($reply->ticket->user_id === Auth::id()) {
            //notif to admins if ticket owner has more questions
            $admins = User::permission('Ticket Operator')->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketReplyAdminNotification($reply->ticket, $reply, Auth::user()->profile));
            }
        } else {
            // send mail if the user commenting is not the ticket owner
            $user = Ticket::find(\request('id'))->user;
            $user->notify(new TicketReplyNotification($reply->ticket, $reply, Auth::user()->profile));
        }

        Log::info('new reply for ticket #' . request('ticket_id') . ',user #' . Auth::id());
        return Responder::success('پاسخ شما به تیکت با موفقیت ارسال شد');
    }

    /**
     * @OA\Put(
     *      tags={"Ticket"},
     *      path="/tickets/{id}/close",
     *      summary="Closes a ticket",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
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
     *     )
     *
     */
    public function close(CloseTicketRequest $request)
    {
        $ticket = Ticket::find(\request('id'));
        $ticket->status = "Closed";
        $ticket->save();
        $ticketOwner = $ticket->user;

        Auth::user()->notify(new TicketStatusNotification($ticketOwner->profile, $ticket));

        Log::info('close ticket #' . request('id') . ',user #' . Auth::id());
        return Responder::success('تیکت شما با موفقیت بسته شد');
    }

    /**
     * @OA\Get(
     *      tags={"Ticket"},
     *      path="/tickets/{id}/show",
     *      summary="Show a ticket information",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
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
     *     )
     *
     */
    public function show(ShowTicketRequest $request)
    {
        $ticket = Ticket::with(['replies', 'replies.user.profile'])->find(\request('id'));
        return Responder::result(['ticket' => $ticket]);
    }
}
