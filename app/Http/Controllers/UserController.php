<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Throwable;

class UserController extends Controller
{
    public function register(RegisterRequest $request) : JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id_user'] = (String) Str::uuid();
            $data['password'] = Hash::make($data['password']);

            $user = new User($data);
            $user->save();
            return (new UserResource($user))->response()->setStatusCode(201);
            } catch(Throwable $e) {
            return response()->json([
                'message' => 'Register failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

        public function login(LoginRequest $request) : JsonResponse {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();

            if(!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'message' => 'Email atau password salah'
                ], 401);
            }

          $user->token = (String)  Str::uuid()->toString();
          $user->save();    
          return (new UserResource($user))->response()->setStatusCode(200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
