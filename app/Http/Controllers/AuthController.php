<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RefreshTokenResource;
use App\Http\Resources\Auth\UserDetailsResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create($request->validated());
            if ($user) {
                # USER REGISTRATION EVENT
                // event(new UserRegistered($user->email, $user->name));
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
            $oClient = PassportClient::where('password_client', 1)->whereRevoked('0')->firstOrFail();
            return $this->getTokenAndRefreshToken($oClient, request('email'), request('password'));
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

    public function getTokenAndRefreshToken(PassportClient $oClient, $email, $password)
    {
        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type'    => 'password',
            'client_id'     => $oClient->id,
            'client_secret' => $oClient->secret,
            'username'      => $email,
            'password'      => $password,
            'scope'         => '*',
        ]);

        if ($response->successful()) {
            return new LoginResource($response->json());
        } else {
            return new JsonResponse(
                ['message' => 'Authentication failed'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $oClient = PassportClient::where('password_client', 1)->first();
        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->token,
            'client_id'     => $oClient->id,
            'client_secret' => $oClient->secret,
            'scope' => '',
        ]);

        if ($response->successful()) {
            return new RefreshTokenResource($response->json());
        } else {
            return new JsonResponse(
                ['message' => $response->json()['message']],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function logout()
    {
        $tokenId = auth()->user()->token()->id;

        // # TO DELETE TOKENS
        // // Token::where('id', $tokenId)->delete();
        // // RefreshToken::where('access_token_id', $tokenId)->delete();

        # TO REVOKE TOKENS
        Token::where('id', $tokenId)->update(['revoked' => 1]);
        RefreshToken::where('access_token_id', $tokenId)->update(['revoked' => 1]);
        return new JsonResponse(
            ['message' => 'Successfully logged out'],
            Response::HTTP_OK
        );
    }
}
