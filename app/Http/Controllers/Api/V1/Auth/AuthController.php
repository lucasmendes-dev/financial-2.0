<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $user->currentAccessToken()->delete();
        
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'user' => new UserResource($user),
                'new_token' => $newToken,
                'token_type' => 'Bearer',
            ]
        ], Response::HTTP_OK);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'You are already logged in',
            'data' => [
                'user' => new UserResource($request->user()),
            ]
        ], Response::HTTP_OK);
    }
}
