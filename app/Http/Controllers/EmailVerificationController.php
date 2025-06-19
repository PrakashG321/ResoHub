<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify($id, $hash): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!hash_equals((string) $hash, sha1($user->personal_email))) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified. Please login.',
                'redirect' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Verification link valid. Please set your password.',
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function setPassword(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!hash_equals($request->hash, sha1($user->personal_email))) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }

        if ($user->password) {
            return response()->json(['message' => 'Password already set.'], 400);
        }

        $user->password = bcrypt($request->password);
        $user->markEmailAsVerified();
        $user->save();

        return response()->json(['message' => 'Password set and email verified successfully. You can now log in.']);
    }
}
