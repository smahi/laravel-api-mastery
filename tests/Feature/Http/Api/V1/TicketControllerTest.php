<?php

use App\Models\User;
use App\Models\Ticket;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use Illuminate\Support\Carbon;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

use Illuminate\Support\Collection;
use Database\Factories\UserFactory;
use Illuminate\Mail\Mailables\Content;

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

it('return a filtered list of tickets', function () {
    $user = User::factory()->create();
    $tickets = Ticket::factory(10)->create();

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author', 'filter[status]' => 'A,C']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200);

    $data = $response->json()['data'];

    expect(count($data))->toBeGreaterThan(0);

    foreach ($data as $key => $value) {
        expect($value['attributes']['status'])->toBeIn(['A', 'C'])->not()->toBeIn(['X', 'H']);
    }
});

it('return a filtered list of tickets by created_at - single date', function () {
    $user = User::factory()->create();
    $ticket1 = Ticket::factory()->create(['created_at' => '2020-12-27']);
    $ticket2 = Ticket::factory()->create(['created_at' => '2022-01-15']);

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author', 'filter[createdAt]' => '2020-12-27']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200);

    $data = $response->json()['data'];
    expect(count($data))->toBe(1);
    expect($data[0]['attributes']['createdAt'])->toContain('2020-12-27');
});

it('return a filtered list of tickets by created_at - range of date', function () {
    $user = User::factory()->create();
    $ticket1 = Ticket::factory()->create(['created_at' => '2020-12-27']);
    $ticket2 = Ticket::factory()->create(['created_at' => '2022-01-15']);
    $ticket3 = Ticket::factory()->create(['created_at' => '2024-11-25']);

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author', 'filter[createdAt]' => '2020-12-27,2023-01-15']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200);

    $data = $response->json()['data'];
    expect(count($data))->toBe(2);
    expect($data[0]['attributes']['createdAt'])->toContain('2020-12-27');
    expect($data[1]['attributes']['createdAt'])->toContain('2022-01-15');
});

it('return a sorted list of tickets', function () {
    $user = User::factory()->create();
    $tickets = Ticket::factory(10)->create();

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author', 'sort' => '-createdAt,status']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200);

    $data = $response->json()['data'];


    $d_ids = collect($data)->map(fn($d) => $d['id']);
    $t_ids = Ticket::query()->orderBy('created_at', 'desc')->orderBy('status', 'asc')->get()->map(fn($t) => $t->id);


    expect($d_ids)->toEqual($t_ids);
});

it('return a filtered list of tickets by matching title', function () {
    $user = User::factory()->create();
    $ticket1 = Ticket::factory()->create(['title' => 'my first ticket']);
    $ticket2 = Ticket::factory()->create(['title' => 'my second ticket']);

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.index', ['include' => 'author', 'filter[title]' => '*first*']))
        // $response = getJson(route('tickets.index'))
        ->assertStatus(200);

    $data = $response->json()['data'];

    expect(count($data))->toEqual(1);
    expect($data[0]['attributes']['title'])->toBe($ticket1->title);
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

it('returns an error if a ticket is not found by id', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('tickets.show', ['ticket' => 11]), [])
        ->assertStatus(404);

    $data = $response->json();

    expect($data['message'])->toBe('Ticket not found.');
});

it('allow creating a ticket', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $ticketData = [
        'data' => [
            'attributes' => [
                'title' => 'my ticket',
                'description' => 'my ticket description',
                'status' => 'A',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'id' => $user->id,
                    ]
                ]
            ]
        ]
    ];

    $response = postJson(route('tickets.store'), $ticketData)
        ->assertStatus(201)
        ->assertExactJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'title',
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

it('allow deleting ticket by id', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    Sanctum::actingAs($user);

    $response = deleteJson(route('tickets.destroy', ['ticket' => $ticket]), [])
        ->assertOk();

    $data = $response->json();

    expect($data['message'])->toBe('Ticket deleted successfully.');
});

it('returns an error if a ticket for deletion is not found', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = deleteJson(route('tickets.destroy', ['ticket' => 11]), [])
        ->assertStatus(404);

    $data = $response->json();

    expect($data['message'])->toBe('Ticket not found.');
});

it('allow replacing a ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    Sanctum::actingAs($user);

    $response = putJson(route('tickets.replace', ['ticket' => $ticket]), [
        'data' => [
            'attributes' => [
                'title' => 'foo',
                'description' => 'foo desc',
                'status' => 'A',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'id' => $user->id,
                    ]
                ]
            ]
        ]
    ])
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'attributes' => [
                    'title',
                    'status',
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'id'
                        ],
                    ],
                ],
            ]
        ]);

    $data = $response->json();


    $updatedTicket = Ticket::find($ticket->id);

    expect($updatedTicket->title)->toBe('foo');
});

it('returns an error when replaing a ticket with invalid data', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    Sanctum::actingAs($user);

    $response = putJson(route('tickets.replace', ['ticket' => $ticket]), [])
        ->assertStatus(422);
});

it('returns an error if ticket for replacing not found', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = putJson(route('tickets.replace', ['ticket' => 11]), [
        'data' => [
            'attributes' => [
                'title' => 'foo',
                'description' => 'foo desc',
                'status' => 'A',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'id' => $user->id,
                    ]
                ]
            ]
        ]
    ])
        ->assertStatus(404);

    $data = $response->json();

    expect($data['message'])->toBe('Ticket not found.');
});


it('allow updating a ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user, 'author')->create();

    Sanctum::actingAs($user);

    $response = patchJson(route('tickets.update', ['ticket' => $ticket]), [
        'data' => [
            'attributes' => [
                'status' => 'C',
            ]
        ]
    ])
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'attributes' => [
                    'title',
                    'status',
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'id'
                        ],
                    ],
                ],
            ]
        ]);

    $data = $response->json();


    $updatedTicket = Ticket::find($ticket->id);

    expect($updatedTicket->status)->toBe('C');
});
