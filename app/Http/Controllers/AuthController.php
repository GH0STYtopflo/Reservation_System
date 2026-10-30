<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (auth()->once($data)) {
            $user = auth()->user();

            if ($user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            return response()->json([
                'token' => base64_encode($user->createToken('auth-token')->plainTextToken),
            ]);
        }

        abort(401, 'Invalid credentials');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data)->refresh();

        return $user->toResource(UserResource::class)->response()->setStatusCode(201);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
    }
}
