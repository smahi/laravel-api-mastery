<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('returns a list of users', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.index'))
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
                    ]
                ]
            ]
        ]);
});

it('return a user for the given user id', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson(route('users.show', ['user' => $user->id]))
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
                ]
            ]
        ]);
});
