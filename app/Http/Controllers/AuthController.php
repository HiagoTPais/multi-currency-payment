<?php

namespace App\Http\Controllers;

use App\Actions\LoginUserAction;
use App\Actions\LogoutUserAction;
use App\Actions\RegisterUserAction;
use App\DTOs\AuthTokenDTO;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $registerUser): JsonResponse
    {
        $user = $registerUser->execute($request->validated());

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => new UserResource($user),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, LoginUserAction $loginUser): JsonResponse
    {
        return $this->respondWithToken($loginUser->execute($request->validated()));
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user('api'));
    }

    public function logout(Request $request, LogoutUserAction $logoutUser): JsonResponse
    {
        $logoutUser->execute($request->user('api'));

        return response()->json([
            'message' => 'Access token revoked successfully.',
        ]);
    }

    private function respondWithToken(AuthTokenDTO $authToken): JsonResponse
    {
        $token = $authToken->token->token;

        return response()->json([
            'access_token' => $authToken->token->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at?->toISOString(),
            'user' => new UserResource($authToken->user),
        ]);
    }
}
