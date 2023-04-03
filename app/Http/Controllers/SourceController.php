<?php

namespace App\Http\Controllers;

use App\Services\SourceService;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    private $sourceService;

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

    public function updateSources()
    {
        $data = $this->sourceService->updateSources();
        $sources_added = count($data);
        $message = $sources_added > 0 ?
            "$sources_added news source(s) added successfully" :
            "News sources already updated";
        return $this->success_response(
            $data,
            $message
        );
    }

    public function getSourceFromAPI()
    {
        return $this->sourceService->getSourceFromAPI();
    }
}
