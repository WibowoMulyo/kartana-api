<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request){
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $data['password'] = bcrypt($data['password']);

            $user = User::create($data);

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Registration failed', 500, ['error' => $e->getMessage()]);
        }
    }

    public function login(Request $request){
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user || !Hash::check($request->password, $user->password) ){
                return $this->error('Invalid credentials', 401);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'User logged in successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Login failed', 500, ['error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request){
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->success(null, 'User logged out successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Logout failed', 500, ['error' => $e->getMessage()]);
        }
    }
}
