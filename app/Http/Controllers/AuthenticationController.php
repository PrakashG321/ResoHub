<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthenticationController extends Controller
{

    public function addMember(StoreUserRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();

            if (strtolower($attributes['role']) === 'admin') {
                return response()->json([
                    'error' => 'You are not authorized to assign the admin role.'
                ], 403);
            }

            $user = User::create($attributes);

            $user->assignRole($attributes['role']);

             event(new Registered($user));

            return response()->json([
                "message" => "User Created Successfully. verificaiton mail sent to personal mail",
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
            $role = $user->getRoleNames()->first();

            return response()->json([
                "message" => "logged in successfully",
                "user" => $user,
                "token" => $token,
                "role" => $role
            ], 200);
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
