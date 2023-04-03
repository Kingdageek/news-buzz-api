<?php

namespace App\DataSources;

use App\DataSources\DataSource;
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
    }

    public function transformResultToPost($data): Post
    {
        return new Post();
    }
    // Error response
    // {
    //     "status": "error",
    //     "code": "apiKeyMissing",
    //     "message": "Your API key is missing. Append this to the URL with the apiKey param, or use the x-api-key HTTP header."
    //     }
    public function fetchPosts(): array
    {
        // GET https://newsapi.org/v2/everything?q=apple&from=2023-04-01&to=2023-04-01&sortBy=popularity
        return [];
    }

    public function transformResultToSource($data): NewsSource
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
        $str_id = $this->getStrId();
        $url = config("datasource")[$str_id]["base_url"] . "top-headlines/sources";

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
        return [];
    }

    public function getStrId(): string
    {
        return "news_api";
    }
}
