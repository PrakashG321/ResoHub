<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{

    public function addMember(StoreUserRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();

            $user = User::create($attributes);

            $user->assignRole($attributes['role']);

            return response()->json([
                "message" => "User Created Successfully",
                "user" => $user
            ], 201);
        } catch (Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 422);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();
            if (!Auth::attempt($attributes)) {
                return response()->json([
                    'error' => 'Invalid credentials'
                ], 401);
            }
            $user = Auth::user();
            $token = $user->createToken("auth_token")->accessToken;
            return response()->json([
                "message" => "logged in successfully",
                "user" => $user,
                "token" => $token
            ],200);
        } catch (Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
                "code" => $error->getCode()
            ], 400);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->token()->revoke();
            return response()->json([
                "message" => "logged out successfully"
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }
}
