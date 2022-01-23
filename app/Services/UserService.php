<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser($data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            event(new Registered($user));
            return $user;
        });
    }

    public function generateAccessToken($user)
    {
        if ($user->hasVerifiedEmail()) {
            $authToken = $user->createToken("auth_token_{$user->id}");
            return [
                'access_token'  => $authToken->plainTextToken,
            ];
        } else {
            return false;
        }
    }

    public function destroyAccessToken(User $user)
    {
        return $user->currentAccessToken()->delete();
    }

    public function updateUser(User $user, $data)
    {
        return DB::transaction(function () use ($data, $user) {
            return $user->update($data);
        });
    }
}
