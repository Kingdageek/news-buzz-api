<?php

namespace App\DataSources;

use App\DataSources\DataSource;
use App\Http\Dtos\FeedRequest;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;
use App\Models\DataSource as DataSourceModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GuzzleHttp\Client;

class NYTDataSource implements DataSource
{
    private $client;
    private $headers;
    private $datasource;
    private $base_url;

    public function __construct()
    {
        $this->client = new Client();
        $this->headers = [
            "Accept" => "application/json",
            // "X-Api-Key" => config("datasource")[$this->getStrId()]['api_key']
        ];
        $str_id = $this->getStrId();
        $datasource = DataSourceModel::where("str_id", $str_id)->first();
        if (!$datasource) {
            throw new ModelNotFoundException("$str_id datasource not found");
        }
        $this->datasource = $datasource;
        $this->base_url = config("datasource")[$this->getStrId()]["base_url"];
    }

    public function transformResultToPost($data): Post
    {
        $post = new Post();
        $post->title = $data["headline"]["main"];
        $post->description = $data["abstract"];
        $main_url = "https://www.nytimes.com/";
        // Take first image from multimedia
        $post->image_url = count($data["multimedia"]) > 0
            ? $main_url . $data["multimedia"][0]["url"] : null;
        $post->source = $data["source"];
        $post->web_url = $data["web_url"];
        $post->date_published = $data["pub_date"];
        $post->category = $data["news_desk"];
        $post->data_source_id = $data["data_source_id"];
        $post->author = $data["byline"]["original"];
        return $post;
    }

    public function fetchPosts(FeedRequest $feedRequest): array
    {
        // GET https://api.nytimes.com/svc/search/v2/articlesearch.json?api-key=lDwNDj0dQ3IULwCIjfLStZFfpmB2XYPP&page=0&maximum=20&fq=source:(%22The%20New%20York%20Times%22)%20AND%20news_desk:(%22Sports%22,%20%22Foreign%22)

        // Restricting to one news source: The NYT -> only categories
        // are of concern
        $api_key = config("datasource")[$this->getStrId()]["api_key"];
        // Pages for NYT uses zero-based indexing
        $feedRequest->page = $feedRequest->page - 1;
        // Very sensitive of quotes too, only double quotes work
        // Don't think this 'minimum' page size works. It always returns
        // 10 articles per request
        $url = $this->base_url . "articlesearch.json?api-key=$api_key&page=" .
            $feedRequest->page . "&minimum=" . $feedRequest->page_size .
            "&fq=source:(\"The New York Times\")";
        $categories = $feedRequest->categories;
        if (isset($categories) && count($categories) > 0) {
            $category_req_str = $this->getCategoriesAsString($categories);
            $url = $url . " AND news_desk:($category_req_str)";
        }
        // dd($categories, $url);
        if (isset($feedRequest->search_keyword)) {
            $url = $url . "&q=" . $feedRequest->search_keyword;
        }

        if (isset($feedRequest->from_date)) {
            // The NYT api requires date to be of format: YYYYmmdd
            $from_date = str_replace("-", "", $feedRequest->from_date);
            $url = $url . "&begin_date=" . $from_date;
        }

        if (isset($feedRequest->to_date)) {
            $to_date = str_replace("-", "", $feedRequest->to_date);
            $url = $url . "&end_date" . $to_date;
        }
        $response = $this->client->request('GET', $url, [
            'headers' => $this->headers
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        // dd($result);
        $posts = [];
        if (isset($result["status"]) && $result["status"] == "OK") {
            foreach ($result['response']['docs'] as $data) {
                $data["data_source_id"] = $this->datasource->id;
                $post = $this->transformResultToPost($data);
                array_push($posts, $post);
            }
        }
        return $posts;
    }

    private function getCategoriesAsString(array $categories): string
    {
        $category_arr = [];
        foreach ($categories as $category) {
            $name = $category->name;
            $name = '"' . $name . '"';
            array_push($category_arr, $name);
        }
        return implode(",", $category_arr);
    }

    public function transformResultToSource($data): NewsSource
    {
        return new NewsSource();
    }

    public function fetchSources(): array
    {
        // The New York Times is the only source. So, to be specified manually
        $newsSource = new NewsSource();
        $newsSource->name = "New York Times";
        $newsSource->description = "Articles from The New York Times";
        $newsSource->web_url = "https://www.nytimes.com";
        $newsSource->str_id = $this->getStrId();
        $newsSource->specialization = "general";
        $newsSource->language = "en";
        $newsSource->country = "us";
        $newsSource->data_source_id = $this->datasource->id;
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
        return false;
    }

    public function searchPosts(FeedRequest $feedRequest): array
    {
        $posts = $this->fetchPosts($feedRequest);
        return $posts;
    }

    public function getStrId(): string
    {
        return "newyork_times";
    }
}
