<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'Incorrect username or password'
            ], 401);
        }

        $token = $user->createToken('apiToken')->plainTextToken;

        $res = [
            'user' => $user,
            'token' => $token,
            'message' => 'Account created successfully!'
        ];

        return response($res, 201);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'avatar' => isset($data['avatar']) ? $data['avatar'] : '/images/avatar/default.png',
            'role' => $data['role'],
            'workspace_id' => auth()->user()->workspace_id ?? 0,
        ]);

        // $user = User::create($request);

        // Generate a verification token
        $token = Str::random(60);

        // Store the token in the users table
        $user->forceFill([
            'verification_token' => $token,
        ])->save();
        
        $redirectUrl = 'http://127.0.0.1:8001/';
        // Send verification email with the token
        $user->sendEmailVerificationNotificationWithToken($token, $redirectUrl);

        $res = [
            'user' => $user,
            'token' => $token,
            'message' => 'Account created successfully!'
        ];
        return response($res, 201);
    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        
        return response()->json(['status'=>'true', 'message'=>'User Logged out!', 'data'=>[]]);
    }
}
