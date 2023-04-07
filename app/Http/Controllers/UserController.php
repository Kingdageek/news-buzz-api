<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateUserPreferences(Request $request)
    {
        $params["sources"] = $request->sources;
        $params["categories"] = $request->categories;
        $user_id = $request->user_id;
        $data = $this->userService->updateUserPreferences(
            $params,
            $user_id
        );
        return $this->success_response(
            $data
        );
    }

    public function getUserPreferences(Request $request)
    {
        $user_id = $request->user_id;
        $preferences = $this->userService->getUserPreferences($user_id);
        return $this->success_response($preferences);
    }
}
