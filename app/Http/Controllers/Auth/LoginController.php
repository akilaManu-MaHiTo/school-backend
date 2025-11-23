<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('userName', $request->userName)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid User Name'], 401);
        } else if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid Password Credentials'], 401);
        }

        if ($user->availability != 1) {
            return response()->json(['message' => 'User Is Not Available'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }
}
