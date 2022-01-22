<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResendVerificationEmailRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::create($request->validated());
                event(new Registered($user));
                return response()->success([], 'You have successfully registered!', Response::HTTP_CREATED);
            });
        } catch (\Throwable $th) {
            return response()->error(
                'Registration failed! Please try again.',
                $th->getMessage(),
                $th->getLine(),
                Response::HTTP_CONFLICT
            );
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (Auth::attempt($request->validated())) {
                if ($request->user()->hasVerifiedEmail()) {
                    $user = $request->user();
                    $authToken = $user->createToken("auth_token_{$user->id}");
                    $response = [
                        'access_token'  => $authToken->plainTextToken,
                    ];
                    return response()->success(new LoginResource($response));
                } else {
                    return response()->error(
                        'Please verify email to proceed',
                        '',
                        '',
                        Response::HTTP_UNAUTHORIZED
                    );
                }
            }
        } catch (\Throwable $th) {
            return response()->error(
                'Invalid credentials',
                $th->getMessage(),
                $th->getLine(),
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    public function me()
    {
        try {
            return new UserResource(auth()->user());
        } catch (\Throwable $th) {
            return response()->error(
                '',
                $th->getMessage(),
                $th->getLine(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                auth()->user()->update($request->validated());
                return response()->success([], 'Profile updated successfully');
            });
        } catch (\Throwable $th) {
            return response()->error(
                'Profile updation failed',
                $th->getMessage(),
                $th->getLine(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function logout()
    {
        try {
            auth()->user()->currentAccessToken()->delete();
            return response()->success([], 'Successfully logged out');
        } catch (\Throwable $th) {
            return response()->error(
                '',
                $th->getMessage(),
                $th->getLine(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function resendVerificationEmail(ResendVerificationEmailRequest $request)
    {
        try {
            $user = User::whereEmail($request->email)->findOrFail();
            if ($user->hasVerifiedEmail()) {
                return response()->success([], 'Email already verified');
            }
            $user->sendEmailVerificationNotification();
            return response()->success([], 'Verification link sent');
        } catch (\Throwable $th) {
            return response()->error(
                'Failed to send verification email',
                $th->getMessage(),
                $th->getLine(),
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
