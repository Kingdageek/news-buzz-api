<?php

namespace App\DataSources;

use App\DataSources\DataSource;
use App\Models\DataSource as DataSourceModel;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TheGuardianDataSource implements DataSource
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

    public function searchPosts(array $params): array
    {
        return [];
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
        $newsSource->name = "The Guardian";
        $newsSource->description = "Articles from The Guardian";
        $newsSource->web_url = "https://www.theguardian.com";
        $newsSource->str_id = $str_id;
        $newsSource->specialization = "general";
        $newsSource->language = "en";
        $newsSource->country = "uk";
        $newsSource->data_source_id = $datasource->id;
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
        return true;
    }

    public function getStrId(): string
    {
        return "the_guardian";
    }
}
