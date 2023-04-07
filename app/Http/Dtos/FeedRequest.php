<?php

namespace App\Http\Dtos;

class FeedRequest
{
    public int $user_id;
    public int $page = 1;
    public int $page_size = 20;
    public string $search_keyword;
    // Array of Source objects [id,str_id,name]
    public array $sources;
    // Array of Category objects [id,name]
    public array $categories;
    // format: YYYY-mm-dd
    public $from_date;
    public $to_date;
    public $sortBy;
}
