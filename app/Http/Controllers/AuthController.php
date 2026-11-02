<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (auth()->once($data)) {
            $user = auth()->user();

            if ($user->role === Role::PATIENT) {
                $profile = $user->patientProfile;
            } elseif ($user->role === Role::DOCTOR) {
                $profile = $user->doctorProfile;
            } else {
                $profile = null;
            }

            if ($user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            return response()->json([
                'token' => base64_encode($user->createToken('auth-token')->plainTextToken),
                'profile' => $profile,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
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

        return Response::noContent();
    }
}
