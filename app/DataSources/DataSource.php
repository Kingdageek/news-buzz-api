<?php

namespace App\DataSources;

use App\Http\Dtos\FeedRequest;
use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;

interface DataSource
{
    // public function transformResultToPost($data): Post;

    /**
     * @return Post[]
     */
    public function fetchPosts(FeedRequest $feedRequest): array;

    // public function transformResultToSource($data): NewsSource;

    /**
     * returns an array of NewsSource objects
     * @return NewsSource[]
     */
    public function fetchSources(): array;

    /**
     * returns an array of lowercased strings i.e. category names
     * @return string[]
     */
    public function fetchCategories(): array;

    public function hasMultipleSources(): bool;

    /**
     * @return Post[]
     */
    public function searchPosts(array $params): array;

    /**
     * Key as defined in the config("datasources") file
     */
    public function getStrId(): string;
}
