<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function updateCategories()
    {
        $data = $this->categoryService->updateCategories();
        $categorys_added = count($data);
        $message = $categorys_added > 0 ?
            "$categorys_added news category(s) added successfully" :
            "News categories already updated";
        return $this->success_response(
            $data,
            $message
        );
    }

    public function getCategoryFromAPI()
    {
        return $this->categoryService->getCategoryFromAPI();
    }
}
