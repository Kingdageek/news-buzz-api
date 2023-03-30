<?php

namespace App\Services;

use App\Models\User;
use App\Utils\Utility;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\DatabaseManager;

class UserService
{
    private $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    public function get_user_by_email($email)
    {
        $user = User::whereEmail($email)->first();
        return $user;
    }

    public function get_user_by_id($id)
    {
        $user = User::whereId($id)->first();
        return $user;
    }

    public function get_all_users()
    {
        return User::all();
    }

    public function change_password(array $params)
    {
        $user = User::findOrFail($params["user_id"]);
        if (!password_verify($params["current_password"], $user->password)) {
            Utility::throwAppFormatted422("Incorrect current password");
        }
        $user->password = Hash::make($params["password"]);
        $user->save();
        return $user;
    }

    public function update(array $params)
    {
        $user = User::where('id', $params['id'])->update([
            'firstname' => $params['firstname'],
            'lastname' => $params['lastname'], 'email' => $params['email'],
        ]);
        // $updated_user = User::where('id', $params['id'])->first();

        return $user;
    }
}
