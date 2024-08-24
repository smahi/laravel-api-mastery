<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'ticket',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                // omit description on all routes, but tickets.show route
                'description' => $this->when($request->routeIs('tickets.show'), $this->description),
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'includes' => new UserResource($this->whenLoaded('author')),
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'user',
                        'id' => $this->author->id,
                    ],
                    'links' => [
                        'self' => 'todo'
                    ]
                ]
            ],
            'links' => [
                'self' => route('tickets.show', ['ticket' => $this->id]),
            ]
        ];
    }
}
