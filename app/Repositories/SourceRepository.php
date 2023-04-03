<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SourceRepository
{

    public function getSpecializationByDataSourceId($data_source_id)
    {
        $query = "SELECT DISTINCT `specialization` FROM `sources`
            WHERE `data_source_id`=$data_source_id";
        return DB::select($query);
    }
}
