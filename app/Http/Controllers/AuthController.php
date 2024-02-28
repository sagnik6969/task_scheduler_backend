<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['logout']);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed|min:8'
        ]);
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
        event(new Registered($user));

        return $user;
    }

    public function handleEmailVerificationRedirect(Request $request)
    {
        $id = $request->id;
        $hash = $request->hash;
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            throw new AuthorizationException();
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?message=Email verified successfully. Please login using your registered email address and password');

        } else
            return abort('email already verified');

        // return redirect('/verify-email/' . $id . '/' . $hash);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($data)) {

            $request->session()->regenerate();

            $user = auth()->user();

            // $user->notify(new AdminLoginNotification());
            return response()->json([
                'user' => $user
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect']
        ]);
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
