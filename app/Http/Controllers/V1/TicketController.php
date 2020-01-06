<?php

# Special thanks to: Wael Salah
# https://webmobtuts.com/backend-development/lets-implement-a-simple-ticketing-system-with-laravel/
namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SSHKey\AddKeyRequest;
use App\Http\Requests\SSHKey\EditKeyRequest;
use App\Repositories\SSHKeyRepository;
use App\Repositories\TicketRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function newTicket(){

    }

    public function newReply(){

    }

    public function myTickets(){

    }

    public function close(){

    }
}
