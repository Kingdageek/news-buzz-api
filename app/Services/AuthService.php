<?php

namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\DatabaseManager;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    private $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    public function register(array $params)
    {
        $plain_password = $params['password'];
        $params['password'] = app('hash')->make($params['password']);
        $this->database->beginTransaction();
        try {
            $user = User::create($params);
            // Login user. If this fails, User can comfortably re-register
            $credentials = ["email" => $user->email, "password" => $plain_password];
            $logged_in_user = $this->login($user, $credentials);
            $this->database->commit();
            return $logged_in_user;
        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function login(User $user, array $credentials)
    {
        try {
            if (!$token = JWTAuth::fromUser($user, $credentials)) {
                return false;
            }
            $user = $user->toArray() + $this->getTokenDetails($token);

            return $user;
        } catch (JWTException $exception) {
            throw $exception;
        }
    }

    public function getTokenDetails($token)
    {
        return array(
            "access_token" => $token,
            "token_type" => "bearer",
            // Expires in 3 hours
            "expires_in" => Auth::factory()->getTTL() * 60 * 60 * 3
        );
    }

    public function refresh()
    {
        return $this->getTokenDetails(Auth::refresh());
    }
}
