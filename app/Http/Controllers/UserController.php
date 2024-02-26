<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Util\Str;
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
}
