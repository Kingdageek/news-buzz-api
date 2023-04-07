<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CategoryRepository
{
    public function getCategoriesWithActiveDataSources()
    {
        $query = "SELECT DISTINCT `c`.`id`, `c`.`name`, `c`.`created_at`, 
        `c`.`updated_at`
        FROM `categories` as `c` INNER JOIN `category_data_source` as `cds` ON
        `c`.`id`=`cds`.`category_id` INNER JOIN `datasources` as `d` ON
        `cds`.`data_source_id`=`d`.`id`
        WHERE `d`.`is_active`=1";
        return DB::select($query);
    }

    public function getCategoriesForUserAndDataSource($user_id, $datasource_id)
    {
        $query = "SELECT `c`.`id`, `c`.`name` FROM `categories` as `c`
        INNER JOIN `user_category` as `uc` ON `c`.`id`=`uc`.`category_id`
        INNER JOIN `category_data_source` AS `cds` ON `cds`.`category_id`=`c`.`id`
        WHERE `cds`.`data_source_id`=$datasource_id and `uc`.`user_id`=$user_id";
        return DB::select($query);
    }
}
