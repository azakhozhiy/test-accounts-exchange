<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!$token = $this->getJwtGuard()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout(): JsonResponse
    {
        $this->getJwtGuard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken($this->getJwtGuard()->refresh());
    }

    public function me(): JsonResponse
    {
        return response()->json($this->getJwtGuard()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->getJwtGuard()->factory()->getTTL() * 60
        ]);
    }
}
