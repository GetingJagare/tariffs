<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function login(Request $request)
    {
        $userData = $request->only(['name', 'password']);

        if (!Auth::attempt($userData, true)) {

            return ['status' => 0];

        }

        return ['status' => 1];
    }

    public function logout(Request $request)
    {

        Auth::logout();

        return ['status' => 1];

    }
}
