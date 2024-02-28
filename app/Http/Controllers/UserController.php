<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Util\Str;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $user = User::all();
        return response()->json($user);
    }
    public function destroy(User $user)
    {
        // Delete the user along with their tasks
        $user->tasks()->delete();
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }


    // public function sendEmailVerificationNotification(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json(['error' => 'User not authenticated.'], 401);
    //     }

    //     if ($user->hasVerifiedEmail()) {
    //         return response()->json(['message' => 'Email already verified.'], 200);
    //     }

    //     $user->sendEmailVerificationNotification();

    //     return response()->json(['message' => 'Verification link sent successfully.'], 200);
    // }
}
