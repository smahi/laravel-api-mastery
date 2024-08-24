<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;


it('all user to login', function () {
    $password = 'Secret.123';
    $user = User::factory()->create(['password' => $password]);

    $loginData = [
        'email' => $user->email,
        'password' => $password,
    ];

    $response = postJson('/api/login', $loginData)
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'message',
            'status'
        ]);
});

it('allow user to register', function () {
    postJson('/api/register', [])
        ->assertStatus(200);
});

it('returns a list of tickets', function () {
    $response = getJson('/api/tickets')
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

it('allow user to logout', function () {
    $user = User::factory()->create();

    // use this in your test to use token instead of session base token
    Sanctum::actingAs($user);


    $response = postJson('/api/logout', [])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'message',
            'status'
        ]);
});
