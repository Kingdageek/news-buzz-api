<?php

namespace App\Services;

use App\Http\Dtos\FeedRequest;
use App\Repositories\CategoryRepository;
use App\Repositories\SourceRepository;

class NewsFeedService
{
    private $dataSourceService;
    private $sourceRepo;
    private $categoryRepo;
    private $userService;

    public function __construct(
        DataSourceService $dataSourceService,
        SourceRepository $sourceRepo,
        CategoryRepository $categoryRepo,
        UserService $userService
    ) {
        $this->dataSourceService = $dataSourceService;
        $this->sourceRepo = $sourceRepo;
        $this->categoryRepo = $categoryRepo;
        $this->userService = $userService;
    }

    public function fetchUserFeed(int $user_id)
    {
        // Try to get feeds from cache first
        $hasUserPreferences = $this->userService->hasUserPreferences($user_id);
        $feed = CacheService::getUserFeed($user_id, $hasUserPreferences);
        if (isset($feed)) {
            return $feed;
        }
        // posts array to gather all posts from all datasources
        $feed = [];
        // get active datasources
        $datasources = $this->dataSourceService->getActiveDataSources();
        // $datasources = [$this->dataSourceService->getDataSourceByStrId("newyork_times")];
        // get sources and categories user is subscribed to for those datasources
        foreach ($datasources as $datasource) {
            // dd($datasource);
            // get related news sources user is subscribed to
            $sources = $this->sourceRepo
                ->getSourcesForUserAndDataSource($user_id, $datasource->id);
            $categories = $this->categoryRepo
                ->getCategoriesForUserAndDataSource($user_id, $datasource->id);
            // dd($sources, $categories);
            // construct the FeedRequest object
            $feedRequest = new FeedRequest();
            $feedRequest->sources = $sources;
            $feedRequest->categories = $categories;
            $feedRequest->user_id = $user_id;
            // use str_id of datasource to get the class from the config
            $datasourceClass = config("datasource")[$datasource->str_id]["class"];
            $datasourceObj = new $datasourceClass();
            // send FeedRequest object to the fetchPosts method of those classes
            $posts = $datasourceObj->fetchPosts($feedRequest);
            // dd($posts);
            // join posts together
            $feed = array_merge($feed, $posts);
        }
        // dd($feed);
        // cache feed
        CacheService::setUserFeed($user_id, $feed, $hasUserPreferences);
        return $feed;
    }
}
