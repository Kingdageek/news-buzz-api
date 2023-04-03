<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DataSourceController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::group(['prefix' => '/v1'], function () {
  Route::group(['namespace' => 'Auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
  });

  // jwt protected Routes
  Route::group(['middleware' => 'jwt.auth'], function() {
    // ADMIN Routes
    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
      // MANAGE DATASOURCES
      Route::patch(
        '/datasources/all/{user_id}',
        [DataSourceController::class, 'updateDataSources']
      );

      // MANAGE SOURCES
      Route::get(
        '/sources/from-api',
        [SourceController::class, 'getSourceFromAPI']
      );
      Route::patch(
        '/sources/all/{user_id}',
        [SourceController::class, 'updateSources']
      );

      // MANAGE CATEGORIES
      Route::get(
        '/categories/from-api',
        [CategoryController::class, 'getCategoryFromAPI']
      );
      Route::patch(
        '/categories/all/{user_id}',
        [CategoryController::class, 'updateCategories']
      );
    });
  });
  
});
