<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('returns a list of users', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.index', ['include' => 'tickets']));

    // dd($response->json()['data'][0]);

    $response
        ->assertOk()
        ->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'email',
                        'emailVerifiedAt',
                        'createdAt',
                        'updatedAt'
                    ],
                    'includes' => [
                        '*' => [
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
                                    'links'
                                ]
                            ],
                            'links'
                        ]

                    ],
                    'links' => [
                        'self'
                    ]
                ]
            ],
            'links',
            'meta'
        ]);
});

it('returns a list of users without tickets', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.index'));

    // dd($response->json()['data'][0]);

    $response
        ->assertOk()
        ->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'email',
                        'emailVerifiedAt',
                        'createdAt',
                        'updatedAt'
                    ],
                    'links' => [
                        'self'
                    ]
                ]
            ],
            'links',
            'meta'
        ]);
});

it('return a user for the given user id', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.show', ['user' => $user->id, 'include' => 'tickets']))
        ->assertOk()
        ->assertExactJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'name',
                    'email',
                    'emailVerifiedAt',
                    'createdAt',
                    'updatedAt'
                ],
                'includes' => [
                    '*' => [
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
                                'links'
                            ]
                        ],
                        'links'
                    ]

                ],
                'links' => [
                    'self'
                ]
            ]
        ]);
});

it('return a user for the given user id without including tickets', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.show', ['user' => $user->id]));

    // dd($response->json());

    $response
        ->assertOk()
        ->assertExactJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'name',
                    'email',
                    'emailVerifiedAt',
                    'createdAt',
                    'updatedAt'
                ],
                'links' => [
                    'self'
                ]
            ]
        ]);
});
