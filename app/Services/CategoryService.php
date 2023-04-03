<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DataSource;
use Illuminate\Database\DatabaseManager;
use App\Repositories\CategoryRepository;

class CategoryService
{
    private $database;
    private $dataSourceService;
    private $categoryRepo;

    public function __construct(
        DatabaseManager $database,
        DataSourceService $dataSourceService,
        CategoryRepository $categoryRepo
    ) {
        $this->database = $database;
        $this->dataSourceService = $dataSourceService;
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Updates the categories table with news categories provided by
     * every available datasource & maps them to their datasources
     */
    public function updateCategories()
    {
        $updated_categories = [];
        $datasources = config("datasource");
        $this->database->beginTransaction();
        try {
            /// fetch news sources each datasource uses for active datasources
            foreach ($datasources as $key => $data) {
                if (!$this->dataSourceService->isActive($key)) continue;
                $datasource = $data["class"];
                $str_id = $key;
                $datasource = new $datasource();
                $categories = $datasource->fetchCategories();
                /// save news sources not already existing
                foreach ($categories as $categoryName) {
                    // check if category with that name already exists
                    $category = Category::whereName($categoryName)->first();
                    if (!$category) {
                        $category = new Category();
                        $category->name = $categoryName;
                        $category->save();
                        array_push($updated_categories, $category);
                    }
                    $datasourceModel = DataSource::where('str_id', $str_id)
                        ->first();
                    $category_datasource = $category->datasources()
                        ->where("data_source_id", $datasourceModel->id)
                        ->first();
                    if (!$category_datasource) {
                        $category->datasources()->attach($datasourceModel->id);
                    }
                }
            }

            $this->database->commit();
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }

        return $updated_categories;
    }

    public function saveCategory(string $categoryName, int $data_source_id)
    {
        // check if category with that name already exists
        $category = Category::whereName($categoryName)->first();
        if (!$category) {
            $category = new Category();
            $category->name = $categoryName;
            $category = $category->save();
        }
        $category->datasources()->attach($data_source_id);
        return $category;
    }

    public function getCategoryFromAPI()
    {
        $datasources = config("datasource");
        // 'news_api', 'the_guardian', 'newyork_times'
        $datasource = $datasources['news_api']['class'];
        // $datasource = $datasources['the_guardian']['class'];
        // $datasource = $datasources['newyork_times']['class'];
        $datasource = new $datasource();
        return $datasource->fetchCategories();
    }

    public function getActiveCategories()
    {
        return $this->categoryRepo->getCategoriesWithActiveDataSources();
    }
}
