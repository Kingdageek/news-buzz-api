<?php

namespace App\Services;

use App\Http\Dtos\NewsSource;
use App\Models\Source;
use App\Repositories\SourceRepository;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\DatabaseManager;

class SourceService
{
    private $database;
    private $dataSourceService;
    private $sourceRepository;

    public function __construct(
        DatabaseManager $database,
        DataSourceService $dataSourceService,
        SourceRepository $sourceRepository
    ) {
        $this->database = $database;
        $this->dataSourceService = $dataSourceService;
    }

    /**
     * Updates the sources table with news sources provided by
     * every available datasource
     */
    public function updateSources()
    {
        $updated_sources = [];
        $datasources = config("datasource");
        $this->database->beginTransaction();
        try {
            /// fetch news sources each datasource uses for active datasources
            foreach ($datasources as $key => $data) {
                if (!$this->dataSourceService->isActive($key)) continue;
                $datasource = $data["class"];
                $datasource = new $datasource();
                $sources = $datasource->fetchSources();
                /// save news sources not already existing
                foreach ($sources as $sourceDto) {
                    $existing_source = Source::where("str_id", $sourceDto->str_id)
                        ->first();
                    if ($existing_source) continue;
                    $new_source = $this->saveSource($sourceDto);
                    array_push($updated_sources, $new_source);
                }
            }

            $this->database->commit();
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }

        return $updated_sources;
    }

    public function saveSource(NewsSource $sourceDto)
    {
        $source = new Source();
        $source->name = $sourceDto->name;
        $source->description = $sourceDto->description;
        $source->language = $sourceDto->language;
        $source->specialization = $sourceDto->specialization;
        $source->country = $sourceDto->country;
        $source->web_url = $sourceDto->web_url;
        $source->str_id = $sourceDto->str_id;
        $source->data_source_id = $sourceDto->data_source_id;
        $source->save();
        return $source;
    }

    public function getSourceFromAPI()
    {
        $datasources = config("datasource");
        // 'news_api', 'the_guardian', 'newyork_times'
        // $datasource = $datasources['news_api']['class'];
        // $datasource = $datasources['the_guardian']['class'];
        $datasource = $datasources['newyork_times']['class'];
        $datasource = new $datasource();
        return $datasource->fetchSources();
    }

    public function getSpecializationByDataSourceId($data_source_id)
    {
        return $this->sourceRepository->getSpecializationByDataSourceId(
            $data_source_id
        );
    }
}
