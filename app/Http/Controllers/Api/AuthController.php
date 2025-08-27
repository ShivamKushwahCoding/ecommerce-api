<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['user' => $user], 201);
    }

    // Login user
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'error' => 'Your account is not activated by admin yet.',
            ], 403);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // Authenticated user
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
