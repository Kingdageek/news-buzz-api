<?php

namespace App\Http\Controllers;

use App\Services\NewsFeedService;
use Illuminate\Http\Request;

class NewsFeedController extends Controller
{
    private $feedService;
    public function __construct(NewsFeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function fetchUserFeed(Request $request) {
        $user_id = $request->user_id;
        $posts = $this->feedService->fetchUserFeed($user_id);
        return $this->success_response($posts);
    }
}
