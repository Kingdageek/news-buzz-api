<?php

namespace App\Services;

use App\Models\DataSource;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DataSourceService
{
    private $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    /**
     * Updates the datasources table with data from the datasources
     * config file
     */
    public function updateDataSources()
    {
        $updated_datasources = [];
        $datasources = config("datasource");
        // dd($datasources);
        $this->database->beginTransaction();
        try {
            foreach ($datasources as $key => $datasource) {
                $existing_datasource = DataSource::where("str_id", $key)->first();
                // dd($key, $existing_datasource);
                if ($existing_datasource) continue;
                $new_datasource = new DataSource();
                $new_datasource->name = $datasource["name"];
                $new_datasource->description = $datasource['description'];
                $new_datasource->base_url = $datasource['base_url'];
                $new_datasource->str_id = $key;
                $new_datasource->is_active = true;
                $new_datasource->save();
                // throw new Exception("Dummy exception");
                array_push($updated_datasources, $new_datasource);
            }
            $this->database->commit();
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }

        return $updated_datasources;
    }

    public function isActive($datasource_str_id)
    {
        $datasource = DataSource::where("str_id", $datasource_str_id)
            ->first();
        if (!$datasource) throw new ModelNotFoundException(
            "No datasource with that string identifier"
        );
        return $datasource->is_active;
    }

    public function getActiveDataSources()
    {
        return DataSource::where("is_active", 1)->get();
    }

    public function getDataSourceByStrId($datasource_str_id)
    {
        return DataSource::where("str_id", $datasource_str_id)->first();
    }

    public function getDataSourceById($datasource_id)
    {
        return DataSource::findOrFail($datasource_id);
    }
}
