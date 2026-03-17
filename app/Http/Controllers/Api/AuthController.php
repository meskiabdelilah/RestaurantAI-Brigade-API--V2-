<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Guid\Fields;

class AuthController extends Controller
{
    use HasApiTokens;
    
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|max:250',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password'])
        ]);

        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,            
        ],201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Bad credential'
            ], 401);
        }


        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

}