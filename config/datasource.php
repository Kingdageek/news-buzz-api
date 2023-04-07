<?php
return [
    "news_api" => [
        "api_key" => env("NEWSAPI_API_KEY"),
        "class" => App\DataSources\NewsApiDataSource::class,
        "description" => "Information from several news sources",
        "name" => "News API",
        "base_url" => "https://newsapi.org/v2/"
    ],
    "the_guardian" => [
        "api_key" => env("THE_GUARDIAN_API_KEY"),
        "class" => App\DataSources\TheGuardianDataSource::class,
        "description" => "Articles from The Guardian",
        "name" => "The Guardian",
        "base_url" => "https://content.guardianapis.com/"
    ],
    "newyork_times" => [
        "api_key" => env("NYT_API_KEY"),
        "class" => App\DataSources\NYTDataSource::class,
        "description" => "Headlines from the New York Times",
        "name" => "The New York Times",
        "base_url" => "https://api.nytimes.com/svc/search/v2/"
    ]
];
