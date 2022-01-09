<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResendVerificationEmailRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\UserDetailsResponse;
use App\Models\User;
use App\Notifications\WelcomeUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create($request->validated());
            if ($user) {
                event(new Registered($user));
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
            if ($request->user()->hasVerifiedEmail()) {
                $user = $request->user();
                $authToken = $user->createToken("auth_token_{$user->id}");
                $response = [
                    'access_token'  => $authToken->plainTextToken,
                ];
                return new LoginResource($response);
            } else {
                return new JsonResponse(
                    ['message' => 'Please verify email to proceed'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
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

    public function resendVerificationEmail(ResendVerificationEmailRequest $request)
    {
        $user = User::whereEmail($request->email)->first();
        
        try {
            if ($user->hasVerifiedEmail()) {
                return new JsonResponse(
                    ['message' => 'Email already verified'],
                    Response::HTTP_OK
                );
            } else {
                $user->sendEmailVerificationNotification();
                return new JsonResponse(
                    ['message' => 'Verification link sent'],
                    Response::HTTP_OK
                );
            }
        } catch (\Throwable $th) {
            return new JsonResponse(
                ['message' => 'Failed to send verification email'],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect('/')->with('already_verified', true);
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            Log::error(new AuthorizationException());
            return redirect('/')->with('verified', false);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('/')->with('verified', true);
    }
}
