<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function checkAuth()
    {
        return response()->json(['auth' => Auth::check(), 'user' => Auth::user()]);
    }

    public function login(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'code' => 'auth_error',
                'message' => 'Ошибка авторизации, попробуйте перезагрузить приложение',
            ], 403);
        }

        $user = User::firstOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'name' => $request->first_name,
                'username' => $request->username,
            ]
        );
        if(!$user->id) {
            $user = User::find($request->id);
        }

        Auth::login($user);
        return response()->json(['user' => $user]);
    }
}
