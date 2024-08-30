<?php

use App\Models\Ticket;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Mail\Mailables\Content;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

it('return a list of tickets', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200)
        // dd($response->json()['data'][0]);
        ->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'type',
                    'id',
                    'attributes' => [
                        'title',
                        'status',
                        'createdAt',
                        'updatedAt',
                    ],
                    'includes' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'email',
                        ],
                        'links' => [
                            'self',
                        ],
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type',
                                'id',
                            ],
                            'links' => [
                                'self',
                            ],
                        ],
                    ],
                    'links' => [
                        'self',
                    ],
                ],
            ],
            'meta',
            'links'
        ]);
});

it('return a ticket for given id', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user, 'author')->create();

    Sanctum::actingAs($user);
    $response = getJson(route('tickets.show', ['ticket' => $ticket->id, 'include' => 'author']))
        ->assertStatus(200)
        ->assertExactJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'title',
                    'description',
                    'status',
                    'createdAt',
                    'updatedAt'
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'type',
                            'id'
                        ],
                        'links' => [
                            'self'
                        ]

                    ],
                ],
                'includes',
                'links' => ['self'],
            ]
        ]);
});

it('return a ticket for given id without including author', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);
    $response = getJson(route('tickets.show', ['ticket' => $ticket->id]))
        ->assertStatus(200)
        ->assertExactJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'title',
                    'description',
                    'status',
                    'createdAt',
                    'updatedAt'
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'type',
                            'id'
                        ],
                        'links' => [
                            'self'
                        ]

                    ],
                ],
                'links' => ['self'],
            ]
        ]);
});
