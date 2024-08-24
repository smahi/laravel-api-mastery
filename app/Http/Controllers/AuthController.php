<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ApiLoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    use ApiResponses;
    public function login(ApiLoginRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken(
                    'API Token for ' . $user->email,
                    ['*'], // set ability for all '*'
                    now()->addMonth() // expire in a month from now
                )->plainTextToken,
            ]
        );
    }

    public function register()
    {
        return $this->ok('register', []);
    }

    public function logout(Request $request)
    {
        // delete all tokens
        // $request->user()->tokens()->delete();

        // delete a token base upon it's ID
        // $request->user()->tokens()->where('id', $tokenId)->delete();

        $request->user()->currentAccessToken()->delete();

        return $this->ok('');
    }
}
