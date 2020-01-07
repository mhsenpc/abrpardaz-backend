<?php

# Special thanks to: Wael Salah
# https://webmobtuts.com/backend-development/lets-implement-a-simple-ticketing-system-with-laravel/
namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Ticket\CloseTicketRequest;
use App\Http\Requests\Ticket\NewReplyRequest;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Http\Requests\Ticket\ShowTicketRequest;
use App\Mailers\AppMailer;
use App\Models\Reply;
use App\Models\Ticket;
use App\Repositories\SSHKeyRepository;
use App\Repositories\TicketRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends BaseController
{
    /**
     * @var TicketRepository
     */
    protected $repository;

    public function __construct(TicketRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $tickets = Ticket::all();
        return responder()->success(['list' => $tickets]);
    }

    public function newTicket(NewTicketRequest $request, AppMailer $mailer)
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

        $mailer->sendTicketInformation(Auth::user(), $ticket);
        return responder()->success(['message' => 'تیکت جدید با موفقیت ایجاد شد']);
    }

    public function newReply(NewReplyRequest $request, AppMailer $mailer)
    {
        $comment = Reply::create([
            'ticket_id' => \request('id'),
            'user_id' => Auth::id(),
            'comment' => \request('comment')
        ]);

        // send mail if the user commenting is not the ticket owner
        if ($comment->ticket->user->id !== Auth::id()) {
            $mailer->sendTicketComments($comment->ticket->user, Auth::user(), $comment->ticket, $comment);
        }

        return responder()->success(['message' => 'پاسخ شما به تیکت با موفقیت ذخیره شد']);
    }

    public function close(CloseTicketRequest $request, AppMailer $mailer)
    {
        $ticket = Ticket::findOrFail(\request('id'));
        $ticket->status = "Closed";
        $ticket->save();
        $ticketOwner = $ticket->user;

        $mailer->sendTicketStatusNotification($ticketOwner, $ticket);

        return responder()->success(['message' => 'تسکت شما با موفقیت بسته شد']);
    }

    public function show(ShowTicketRequest $request)
    {
        $ticket = $this->repository->with(['replies'])->find(\request('id'));
        return responder()->success(['ticket' => $ticket]);
    }
}
