<?php

namespace App\DataSources;

use App\DataSources\DataSource;
use App\Http\Dtos\FeedRequest;
use App\Models\DataSource as DataSourceModel;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TheGuardianDataSource implements DataSource
{
    private $client;
    private $headers;
    private $base_url;
    private $datasource;

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
        $this->base_url = config("datasource")[$str_id]["base_url"];
    }

    private function transformResultToPost($data): Post
    {
        $post = new Post();
        $post->title = $data["webTitle"];
        $post->source = $this->datasource->name;
        $post->date_published = $data["webPublicationDate"];
        $post->category = $data["sectionName"];
        $post->web_url = $data["webUrl"];
        $post->data_source_id = $this->datasource->id;
        return $post;
    }

    public function searchPosts(array $params): array
    {
        return [];
    }

    public function fetchPosts(FeedRequest $feedRequest): array
    {
        // https://content.guardianapis.com/search?q=12%20years%20a%20slave&format=json&tag=film/film,tone/reviews&from-date=2010-01-01&show-tags=contributor&show-fields=starRating,headline,thumbnail,short-url&show-refinements=all&order-by=relevance

        // This just has one News source. Only categories will be considered
        $categories = $feedRequest->categories;
        $api_key = config("datasource")[$this->getStrId()]["api_key"];
        $url = $this->base_url . "search?api-key=$api_key" . "&page=" .
            $feedRequest->page . "&page-size=" . $feedRequest->page_size;

        if (isset($categories) && count($categories) > 0) {
            $category_req_str = $this->getCategoriesAsString($categories);
            $url = $url . "&section=$category_req_str";
        }
        $response = $this->client->request('GET', $url, [
            'headers' => $this->headers
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        $result = $result["response"];
        // dd($result["response"], $url);
        $posts = [];
        if (isset($result["status"]) && $result["status"] == "ok") {
            foreach ($result['results'] as $data) {
                $data["data_source_id"] = $this->datasource->id;
                $post = $this->transformResultToPost($data);
                array_push($posts, $post);
            }
        }
        return $posts;
    }

    private function getCategoriesAsString(array $categories)
    {
        /**
         * The guardian uses boolean operators
         * ',' for AND. '|' or OR. '-' for NOT
         */
        $category_arr = [];
        foreach ($categories as $category) {
            array_push($category_arr, $category->name);
        }
        $request_str = implode("|", $category_arr);
        return $request_str;
    }

    // private function transformResultToSource($data): NewsSource
    // {
    //     return new NewsSource();
    // }

    public function fetchSources(): array
    {
        // The Guardian is the only source. So, to be specified manually
        $newsSource = new NewsSource();
        $newsSource->name = "The Guardian";
        $newsSource->description = "Articles from The Guardian";
        $newsSource->web_url = "https://www.theguardian.com";
        $newsSource->str_id = $this->getStrId();
        $newsSource->specialization = "general";
        $newsSource->language = "en";
        $newsSource->country = "uk";
        $newsSource->data_source_id = $this->datasource->id;
        return [$newsSource];
    }

    /**
     * returns an array of capitalized strings i.e. category names
     */
    public function fetchCategories(): array
    {
        // url: https://content.guardianapis.com/sections
        $str_id = $this->getStrId();
        $datasource = config("datasource")[$str_id];
        $api_key = $datasource["api_key"];
        $url = $datasource['base_url'] . "sections?api-key=" . $api_key;
        $response = $this->client->request('GET', $url, [
            'headers' => $this->headers
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        $result = $result["response"];
        // dd($result);
        $categories = [];
        if (isset($result["status"]) && $result["status"] == "ok") {
            foreach ($result['results'] as $data) {
                $category_name = strtolower($data["webTitle"]);
                array_push($categories, $category_name);
            }
        }
        return $categories;
    }

    public function hasMultipleSources(): bool
    {
        return false;
    }

    public function getStrId(): string
    {
        return "the_guardian";
    }
}
