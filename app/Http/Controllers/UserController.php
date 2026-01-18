<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($request->user()),
        ], 200);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id_user'] = (string) Str::uuid();
            $data['password'] = Hash::make($data['password']);

            $user = new User($data);
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => new UserResource($user),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Register failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'message' => 'Email atau password salah',
                ], 401);
            }
            $token = $user->createToken('api-token')->plainTextToken;
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => (new UserResource($user))->withToken($token),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        $user = User::where('token', $token)->first();
        $token = $request->bearerToken();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid token',
            ], 401);
        }

        $user->token = null;
        $user->save();

        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
