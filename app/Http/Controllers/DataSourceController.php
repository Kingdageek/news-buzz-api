<?php

namespace App\Http\Controllers;

use App\Services\DataSourceService;
use Illuminate\Http\Request;

class DataSourceController extends Controller
{
    private $dataSourceService;

    public function __construct(DataSourceService $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    public function updateDataSources()
    {
        $data = $this->dataSourceService->updateDataSources();
        $sources_added = count($data);
        $message = $sources_added > 0 ?
            "$sources_added datasource(s) added successfully" :
            "Datasources already updated";
        return $this->success_response(
            $data,
            $message
        );
    }
}
