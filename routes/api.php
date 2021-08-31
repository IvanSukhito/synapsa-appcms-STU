<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Login Only
Route::group(['middleware' => ['jwtToken']], function () use ($router) {
    Route::post('check-login', ['uses' => 'App\Http\Controllers\API\V1\HomeController@login'])->name('api.check-login');
   
    Route::get('article', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticle'])->name('api.article');
    Route::get('article/{id}', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticleDetail'])->name('api.article');


});
// Non Login
   
Route::post('login', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@login'])->name('api.login');
Route::get('logout', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@logout'])->name('api.logout');

Route::post('sign-up', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@signUp'])->name('api.sign-up');
Route::get('search-klinik', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchKlinik'])->name('api.search-klinik');
Route::get('search-city', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchCity'])->name('api.search-city');
Route::get('search-district', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchDistrict'])->name('api.search-district');
Route::get('search-subdistrict', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchSubdistrict'])->name('api.search-subdistrict');

Route::post('forgot-password', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@forgotPassword'])->name('api.forgot-password');

Route::get('version', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@version'])->name('api.version');
Route::post('version', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@compareVersion'])->name('api.compareVersion');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
