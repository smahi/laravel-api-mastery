<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Ticket;
use App\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Policies\V1\TicketPolicy;
use App\Traits\ApiResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketController extends ApiBaseController
{
    use ApiResponses;

    protected $policyClass = TicketPolicy::class;

    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filter)
    {

        if ($this->include('author')) {
            return TicketResource::collection(Ticket::with('author')->filter($filter)->paginate());        //
        }

        return TicketResource::collection(Ticket::filter($filter)->paginate());        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {
            $user = User::findOrFail($request->input('data.relationships.author.data.id'));
        } catch (ModelNotFoundException $e) {
            return $this->ok('User not found', [
                'error' => 'The provided user ID does not exist.'
            ]);
        }

        return new TicketResource(Ticket::create($request->mappedAttributes())->load('author'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->include('author')) {
                return new TicketResource($ticket->load('author'));        //
            }
            return new TicketResource($ticket);        //
        } catch (ModelNotFoundException $e) {
            return $this->error('Ticket not found.', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            $ticket->update($request->mappedAttributes());

            // policy
            //$this->isAble('update', $ticket);
            $this->isAble('update', $ticket);

            return new TicketResource($ticket);
        } catch (ModelNotFoundException $e) {
            return $this->error('Ticket not found.', 404);
        } catch (AuthorizationException $e) {
            return $this->error('You are not authorized to update this ticket', 401);
        }
    }

    public function replace(ReplaceTicketRequest $request, int $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);


            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        } catch (ModelNotFoundException $e) {
            return $this->error('Ticket not found.', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            $ticket->delete();

            return $this->ok('Ticket deleted successfully.', []);
        } catch (ModelNotFoundException $e) {
            return $this->error('Ticket not found.', 404);
        }
    }
}
