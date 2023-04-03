<?php

namespace App\DataSources;

use App\DataSources\DataSource;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;
use App\Models\DataSource as DataSourceModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GuzzleHttp\Client;

class NYTDataSource implements DataSource
{
    private $client;
    private $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->headers = [
            "Accept" => "application/json",
            // "X-Api-Key" => config("datasource")[$this->getStrId()]['api_key']
        ];
    }

    public function transformResultToPost($data): Post
    {
        return new Post();
    }

    public function fetchPosts(): array
    {
        return [];
    }

    public function transformResultToSource($data): NewsSource
    {
        return new NewsSource();
    }

    public function fetchSources(): array
    {
        $str_id = $this->getStrId();
        $datasource = DataSourceModel::where("str_id", $str_id)->first();
        if (!$datasource) {
            throw new ModelNotFoundException("$str_id datasource not found");
        }
        // The Guardian is the only source. So, to be specified manually
        $newsSource = new NewsSource();
        $newsSource->name = "New York Times";
        $newsSource->description = "Articles from The New York Times";
        $newsSource->web_url = "https://www.nytimes.com";
        $newsSource->str_id = $str_id;
        $newsSource->specialization = "general";
        $newsSource->language = "en";
        $newsSource->country = "us";
        $newsSource->data_source_id = $datasource->id;
        return [$newsSource];
    }

    public function fetchCategories(): array
    {
        // No endpoint for this. Just the values on the doc page
        $news_desk = [
            "Adventure Sports",
            "Arts & Leisure",
            "Arts",
            "Automobiles",
            "Blogs",
            "Books",
            "Booming",
            "Business Day",
            "Business",
            "Cars",
            "Circuits",
            "Classifieds",
            "Connecticut",
            "Crosswords & Games",
            "Culture",
            "DealBook",
            "Dining",
            "Editorial",
            "Education",
            "Energy",
            "Entrepreneurs",
            "Environment",
            "Escapes",
            "Fashion & Style",
            "Fashion",
            "Favorites",
            "Financial",
            "Flight",
            "Food",
            "Foreign",
            "Generations",
            "Giving",
            "Global Home",
            "Health & Fitness",
            "Health",
            "Home & Garden",
            "Home",
            "Jobs",
            "Key",
            "Letters",
            "Long Island",
            "Magazine",
            "Market Place",
            "Media",
            "Men's Health",
            "Metro",
            "Metropolitan",
            "Movies",
            "Museums",
            "National",
            "Nesting",
            "Obits",
            "Obituaries",
            "Obituary",
            "OpEd",
            "Opinion",
            "Outlook",
            "Personal Investing",
            "Personal Tech",
            "Play",
            "Politics",
            "Regionals",
            "Retail",
            "Retirement",
            "Science",
            "Small Business",
            "Society",
            "Sports",
            "Style",
            "Sunday Business",
            "Sunday Review",
            "Sunday Styles",
            "T Magazine",
            "T Style",
            "Technology",
            "Teens",
            "Television",
            "The Arts",
            "The Business of Green",
            "The City Desk",
            "The City",
            "The Marathon",
            "The Millennium",
            "The Natural World",
            "The Upshot",
            "The Weekend",
            "The Year in Pictures",
            "Theater",
            "Then & Now",
            "Thursday Styles",
            "Times Topics",
            "Travel",
            "U.S.",
            "Universal",
            "Upshot",
            "UrbanEye",
            "Vacation",
            "Washington",
            "Wealth",
            "Weather",
            "Week in Review",
            "Week",
            "Weekend",
            "Westchester",
            "Wireless Living",
            "Women's Health",
            "Working",
            "Workplace",
            "World",
            "Your Money"
        ];
        $news_desk = array_map(function ($categoryName) {
            return strtolower($categoryName);
        }, $news_desk);
        return $news_desk;
    }

    public function hasMultipleSources(): bool
    {
        return true;
    }

    public function searchPosts(array $params): array
    {
        return [];
    }

    public function getStrId(): string
    {
        return "newyork_times";
    }
}
