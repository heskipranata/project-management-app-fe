<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function register (Request $request){
        
        $request->validate([
            'name' => 'required|string',
            'email' =>'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message'=> 'User registered successfully']);
    }

    public function  login(Request $request){
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login'], 401);
        }

        return response()->json([
            'message' => 'Login successfully',
            'token' => $token,
        ]);
    }

    public function profile(){
        return response()->json(Auth::user());
    }

    public function logout() {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['message' => 'Logged out']);
    }
}

