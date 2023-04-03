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
}
