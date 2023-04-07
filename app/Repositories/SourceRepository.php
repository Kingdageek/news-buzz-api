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

    public function getSourcesWithActiveDataSources()
    {
        $query = "SELECT `s`.`id`, `s`.`name`, `s`.`description`, `s`.`language`,
        `s`.`data_source_id`, `s`.`specialization`, `s`.`country`, `s`.`web_url`,
        `s`.`str_id`, `s`.`created_at`, `s`.`updated_at`
        FROM `sources` as `s` INNER JOIN `datasources` as `d` ON
        `s`.`data_source_id`=`d`.`id`
        WHERE `d`.`is_active`=1";
        return DB::select($query);
    }

    public function getSourcesForUserAndDataSource($user_id, $datasource_id)
    {
        $query = "SELECT `s`.`id`, `s`.`name`, `s`.`str_id` FROM `sources` as `s`
        INNER JOIN `user_source` as `us` ON `s`.`id`=`us`.`source_id`
        WHERE `s`.`data_source_id`=$datasource_id and `us`.`id`=$user_id";
        return DB::select($query);
    }
}
