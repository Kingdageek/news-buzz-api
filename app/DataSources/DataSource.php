<?php

namespace App\DataSources;

use App\Http\Dtos\Post;
use App\Http\Dtos\NewsSource;

interface DataSource
{
    public function transformResultToPost($data): Post;

    public function fetchPosts(): array;

    public function transformResultToSource($data): NewsSource;

    /**
     * returns an array of NewsSource objects
     */
    public function fetchSources(): array;

    /**
     * returns an array of lowercased strings i.e. category names
     */
    public function fetchCategories(): array;

    public function hasMultipleSources(): bool;

    public function searchPosts(array $params): array;

    /**
     * Key as defined in the config("datasources") file
     */
    public function getStrId(): string;
}
