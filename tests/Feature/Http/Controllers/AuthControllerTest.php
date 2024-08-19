<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('return a message and 200 status', function () {
    $loginData = [
        'email' => 'john@example.com',
        'password' => 'secret',
    ];

    $response = post('/api/login', $loginData)
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'status'
        ]);
});

it('register a user', function () {
    post('/api/register', [])
        ->assertStatus(200);
});

it('return a list of tickets', function () {
    $response = get('/api/tickets')
        ->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'user_id',
                'title',
                'description',
                'status'
            ]
        ]);
});
