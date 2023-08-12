<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;

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
        } else
             if(!$user->is_active) return $this->jsonResponse(false, "Account is not activated!", 403);

        $token = $user->createToken('apiToken')->plainTextToken;

        $res = [
            'user' => $user,
            'token' => $token,
            'message' => 'Logged in successfully!'
        ];

        return response($res, 201);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required',
            'avatar'    => 'required',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'avatar' => $data['avatar'],
            'role' => $data['role'],
            'workspace_id' => auth()->user()->workspace_id ?? 0,
        ]);

        // Generate a verification token
        $token = Str::random(60);

        // Store the token in the users table
        $user->forceFill([
            'remember_token' => $token,
        ])->save();
        
        // Send verification email with the token
        $password = $data['password'];
        $user->sendEmailVerificationNotificationWithToken($token, $password);

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

    public function forgotPassword (Request $request){
        
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status)
            ];
        }

        // throw ValidationException::withMessages([
        //     'email' => [trans($status)],
        // ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message'=> 'Password reset successfully'
            ]);
        }

        return response([
            'message'=> __($status)
        ], 500);

    }
}
