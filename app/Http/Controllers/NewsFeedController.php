<?php

namespace App\Http\Controllers;

use App\Http\Dtos\FeedRequest;
use App\Services\NewsFeedService;
use App\Utils\Utility;
use Illuminate\Http\Request;

class NewsFeedController extends Controller
{
    private $feedService;
    public function __construct(NewsFeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function fetchUserFeed(Request $request)
    {
        $user_id = $request->user_id;
        $posts = $this->feedService->fetchUserFeed($user_id);
        return $this->success_response($posts);
    }

    public function searchFeed(Request $request)
    {
        if (
            !isset($request->q) || !isset($request->source)
        ) {
            Utility::throwAppFormatted422("Search query or source missing");
        }
        // construct the FeedRequest object
        $feedRequest = new FeedRequest();
        $feedRequest->sources = [$request->source];
        $feedRequest->categories = isset($request->category) ?
            [$request->category] : [];
        $feedRequest->from_date = $request->from_date;
        $feedRequest->to_date = $request->to_date;
        $feedRequest->search_keyword = $request->q;
        $posts = $this->feedService->searchFeed($feedRequest);
        return $this->success_response($posts);
    }
}
