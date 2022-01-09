<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\UserDetailsResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create($request->validated());
            if ($user) {
                return new JsonResponse(
                    ['message' => 'You have successfully registered!'],
                    Response::HTTP_OK
                );
            }
            return new JsonResponse(
                ['message' => 'Registration failed! Please try again.'],
                Response::HTTP_CONFLICT
            );
        });
    }

    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $user = $request->user();
            $authToken = $user->createToken("auth_token_{$user->id}");
            $response = [
                'access_token'  => $authToken->plainTextToken,
            ];
            return new LoginResource($response);
        } else {
            return new JsonResponse(
                ['message' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    public function me()
    {
        return new UserDetailsResponse(auth()->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        if (auth()->user()->update($request->validated())) {
            return new JsonResponse(
                ['message' => 'Profile updated successfully'],
                Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                ['message' => 'Profile updation failed'],
                Response::HTTP_CONFLICT
            );
        }
    }

    public function logout()
    {
        if (auth()->user()->currentAccessToken()->delete()) {
            return new JsonResponse(
                ['message' => 'Successfully logged out'],
                Response::HTTP_OK
            );
        }
    }
}
