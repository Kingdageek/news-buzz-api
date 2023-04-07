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

    public function updateUserPreferences(array $params, int $user_id)
    {
        $user = User::findOrFail($user_id);
        $categories = $params["categories"];
        $sources = $params["sources"];
        $category_ids = [];
        foreach ($categories as $category) {
            array_push($category_ids, $category["id"]);
        }
        $source_ids = [];
        foreach ($sources as $source) {
            array_push($source_ids, $source["id"]);
        }
        $this->database->beginTransaction();
        try {
            // detach first incase already attached
            $user->categories()->detach($category_ids);
            $user->sources()->detach($source_ids);
            // attach the desired categories and sources to the user
            $user->categories()->attach($category_ids);
            $user->sources()->attach($source_ids);

            // invalidate user feed cache on preference update
            CacheService::deleteUserFeed($user_id);
            $this->database->commit();
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }
        return $params;
    }

    public function getUserPreferences(int $user_id): array
    {
        $user = User::findOrFail($user_id);
        $preferences["categories"] = $user->categories()->get();
        $preferences["sources"] = $user->sources()->get();
        return $preferences;
    }

    public function hasUserPreferences(int $user_id): bool
    {
        $preferences = $this->getUserPreferences($user_id);
        $categories = $preferences["categories"];
        $sources = $preferences["sources"];
        if (count($categories) > 0 && count($sources) > 0) {
            return true;
        }
        return false;
    }
}
