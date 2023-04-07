<?php

namespace App\Console\Commands;

use App\Services\CategoryService;
use App\Services\DataSourceService;
use App\Services\SourceService;
use Illuminate\Console\Command;

class UpdateMainEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-main-entities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates or creates datasources, news sources, & categories in the database';

    private $categoryService;
    private $dataSourceService;
    private $sourceService;

    public function __construct(
        CategoryService $categoryService,
        DataSourceService $dataSourceService,
        SourceService $sourceService
    ) {
        parent::__construct();
        $this->categoryService = $categoryService;
        $this->dataSourceService = $dataSourceService;
        $this->sourceService = $sourceService;
    }
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Has to happen in this order
        $updated_datasources = $this->dataSourceService->updateDataSources();
        $updated_sources = $this->sourceService->updateSources();
        $updated_categories = $this->categoryService->updateCategories();

        echo count($updated_datasources) . " datasources updated\n";
        echo count($updated_sources) . " sources updated\n";
        echo count($updated_categories) . " categories updated\n";
    }
}
