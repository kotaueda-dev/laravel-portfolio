<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function deleteAccount()
    {
        $user = Auth::user();

        if ($user) {
            $user->delete();
            return response()->json(['message' => 'Account deleted successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
