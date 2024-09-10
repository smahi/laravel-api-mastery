<?php

namespace App\Policies\V1;

use App\Models\User;
use App\Models\Ticket;

class TicketPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // the funciton name matches the ability name, here 'update'
    public function update(User $user, Ticket $ticket)
    {
        return $user->id === $ticket->user_id;
    }
}
