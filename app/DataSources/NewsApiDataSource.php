<?php

namespace App\DataSources;

use App\DataSources\DataSource;
use App\Http\Dtos\FeedRequest;
use App\Models\DataSource as DataSourceModel;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\SourceRepository;

class NewsApiDataSource implements DataSource
{
    private $client;
    private $headers;
    private $datasource;
    private $base_url;
    // NewsAPI only allows 20 max sources
    const MAX_ALLOWED_SOURCES = 20;

    public function __construct()
    {
        $str_id = $this->getStrId();
        $this->client = new Client();
        $this->headers = [
            "Accept" => "application/json",
            "X-Api-Key" => config("datasource")[$str_id]['api_key']
        ];
        $datasource = DataSourceModel::where("str_id", $str_id)->first();
        if (!$datasource) {
            throw new ModelNotFoundException("$str_id datasource not found");
        }
        $this->datasource = $datasource;
        $this->base_url = config("datasource")[$str_id]['base_url'];
    }

    private function transformResultToPost($data): Post
    {
        $post = new Post();
        $post->title = $data["title"];
        $post->author = $data["author"];
        $post->source = $data["source"]["name"];
        $post->content = $data["content"];
        $post->web_url = $data["url"];
        $post->image_url = $data["urlToImage"];
        $post->data_source_id = $data["data_source_id"];
        $post->date_published = $data["publishedAt"];
        $post->description = $data["description"];
        return $post;
    }
    // Error response
    // {
    //     "status": "error",
    //     "code": "apiKeyMissing",
    //     "message": "Your API key is missing. Append this to the URL with the apiKey param, or use the x-api-key HTTP header."
    //     }
    public function fetchPosts(FeedRequest $feedRequest): array
    {
        // GET https://newsapi.org/v2/everything?sources=bbc-news
        // if user has no references. Do a general category search of top-headlines
        // if user has preferences, search the 'everything' endpoint
        // use sources if available, otherwise use category or nothing if none

        $url = $this->base_url;
        if (count($feedRequest->sources) == 0 && count($feedRequest->categories) == 0) {
            $url = $url . "top-headlines?pageSize=" .
                $feedRequest->page_size . "&page=" . $feedRequest->page .
                "&category=general&language=en";
        } else {
            $url = $url . "everything?pageSize=" .
                $feedRequest->page_size . "&page=" . $feedRequest->page;
        }

        if (isset($feedRequest->sources) && count($feedRequest->sources) > 0) {
            // comma-separated string list of sources
            $sources = $this->getSourcesAsString($feedRequest->sources);
            $url = $url . "&sources=$sources";
        } else if (isset($feedRequest->categories) && count($feedRequest->categories) > 0) {
            // with categories, simply do a search
            $keywords = $this->getCategoriesAsString($feedRequest->categories);
            $url = $url . "&q=$keywords";
        }
        $response = $this->client->request('GET', $url, [
            'headers' => $this->headers
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        // dd($result);
        $posts = [];
        if (isset($result["status"]) && $result["status"] == "ok") {
            foreach ($result['articles'] as $data) {
                $data["data_source_id"] = $this->datasource->id;
                $post = $this->transformResultToPost($data);
                array_push($posts, $post);
            }
        }
        return $posts;
    }

    private function getCategoriesAsString(array $categories): string
    {
        // with categories, simply do a search
        $category_arr = [];
        foreach ($categories as $category) {
            array_push($category_arr, $category->name);
        }
        // restrict to 100 characters
        // dd($categories);
        $keywords = substr(implode(" ", $category_arr), 0, 100);
        return $keywords;
    }

    private function getSourcesAsString(array $sources): string
    {
        $str_ids = [];
        for ($i = 0; $i <  self::MAX_ALLOWED_SOURCES; $i++) {
            $source = $sources[$i];
            array_push($str_ids, $source->str_id);
        }
        return implode(",", $str_ids);
    }

    private function formFetchPostsUrl(FeedRequest $feedRequest): string
    {
        $url_arr = [];

        return "";
    }

    private function transformResultToSource($data): NewsSource
    {
        $newsSource = new NewsSource();
        $newsSource->name = $data['name'];
        $newsSource->description = $data['description'];
        $newsSource->web_url = $data['url'];
        $newsSource->str_id = $data['id'];
        $newsSource->specialization = $data['category'];
        $newsSource->language = $data['language'];
        $newsSource->country = $data['country'];
        $newsSource->data_source_id = $data["data_source_id"];
        return $newsSource;
    }

    public function fetchSources(): array
    {
        $url = $this->base_url . "top-headlines/sources";

        // GET https://newsapi.org/v2/top-headlines/sources
        $response = $this->client->request('GET', $url, [
            'headers' => $this->headers
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        $sources = [];
        if (isset($result["status"]) && $result["status"] == "ok") {
            foreach ($result['sources'] as $data) {
                $data["data_source_id"] = $this->datasource->id;
                $source = $this->transformResultToSource($data);
                array_push($sources, $source);
            }
        }
        // dd($result);
        return $sources;
    }

    public function fetchCategories(): array
    {
        $sourceRepository = new SourceRepository();
        $categories = $sourceRepository->getSpecializationByDataSourceId(
            $this->datasource->id
        );
        // dd($categories);
        $categories = array_map(function ($category) {
            return strtolower($category->specialization);
        }, $categories);
        return $categories;
    }

    public function hasMultipleSources(): bool
    {
        return true;
    }

    public function searchPosts(array $params): array
    {
        // GET https://newsapi.org/v2/everything?q=apple&from=2023-04-01&to=2023-04-01&sortBy=popularity
        return [];
    }

    public function getStrId(): string
    {
        return "news_api";
    }
}
