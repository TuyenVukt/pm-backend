<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class EmailVerificationController extends Controller
{

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Already Verified'
            ];
        }

        $request->user()->sendEmailVerificationNotificationWithToken($token);

        return ['status' => 'verification-link-sent'];
    }

    public function verify(Request $request, $id, $token)
    {
        // Find the user by ID
        $user = User::find($id);
        
        // Check if the user exists
        if ($user) {
            // Check if the verification token matches
            if (hash_equals($user->verification_token, $token)) {
                $user->markEmailAsVerified();
                $user->verification_token = null;
                $user->is_active = true;
                $user->save();
    
                return redirect('http://127.0.0.1:8001/');
            }
        }
    
        return response()->json(['error' => 'Invalid verification link.'], 400);
    }
}

