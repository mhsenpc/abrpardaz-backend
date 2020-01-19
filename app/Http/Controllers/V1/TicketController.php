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
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReplyNotification;
use App\Notifications\TicketStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $tickets = Ticket::all();
        return responder()->success(['list' => $tickets]);
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
        return responder()->success(['list' => $tickets]);
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
     *             type="int"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
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

        Auth::user()->notify(new NewTicketNotification($ticket,Auth::user()->profile));
        return responder()->success(['message' => 'تیکت جدید با موفقیت ایجاد شد']);
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
     *             type="int"
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

        // send mail if the user commenting is not the ticket owner
        if ($reply->ticket->user->id !== Auth::id()) {
            Auth::user()->notify(new TicketReplyNotification($reply->ticket, $reply, Auth::user()->profile));
        }

        return responder()->success(['message' => 'پاسخ شما به تیکت با موفقیت ذخیره شد']);
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
     *             type="int"
     *         )
     *     ),
     *     )
     *
     */
    public function close(CloseTicketRequest $request)
    {
        $ticket = Ticket::findOrFail(\request('id'));
        $ticket->status = "Closed";
        $ticket->save();
        $ticketOwner = $ticket->user;

        Auth::user()->notify(new TicketStatusNotification($ticketOwner->profile, $ticket));

        return responder()->success(['message' => 'تسکت شما با موفقیت بسته شد']);
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
     *             type="int"
     *         )
     *     ),
     *     )
     *
     */
    public function show(ShowTicketRequest $request)
    {
        $ticket = Ticket::with(['replies'])->find(\request('id'));
        return responder()->success(['ticket' => $ticket]);
    }
}
