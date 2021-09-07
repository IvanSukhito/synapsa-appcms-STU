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

    Route::post('check-login', ['uses' => 'App\Http\Controllers\API\V1\HomeController@checkLogin'])->name('api.check-login');
    $router->group(['prefix' => 'profile'], function () use ($router) {
        $router->get('/', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@profile'])->name('api.user.profile');
        $router->post('/', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@updateProfile'])->name('api.user.updateProfile');
        $router->post('photo', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@updatePhoto'])->name('api.user.updatePhoto');
        $router->post('password', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@updatePassword'])->name('api.user.updatePassword');
        $router->post('address', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@updateAddress'])->name('api.user.updateAddress');
        $router->get('address', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@getAddress'])->name('api.user.getAddress');
        $router->post('verification-email', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@verifEmail'])->name('api.user.verifEmail');
        $router->post('verification-phone', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@verifPhone'])->name('api.user.verifPhone');
        $router->get('notification', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@notifications'])->name('api.user.notifications');
    });
    Route::get('home', ['uses' => 'App\Http\Controllers\API\V1\HomeController@home'])->name('api.home');
    Route::get('', ['uses' => 'App\Http\Controllers\API\V1\HomeController@notifications'])->name('api.notifications');

    Route::get('article', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticle'])->name('api.getArticle');
    Route::get('article/{id}', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticleDetail'])->name('api.getArticleDetail');

    Route::get('search-product', ['uses' => 'App\Http\Controllers\API\V1\ProductController@searchProduct'])->name('api.searchProduct');
    Route::get('product', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getProduct'])->name('api.getProduct');
    Route::get('product/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getProductDetail'])->name('api.getProductDetail');


    Route::get('lab', ['uses' => 'App\Http\Controllers\API\V1\LabController@getLab'])->name('api.getLab');
    Route::get('lab/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@getLabDetail'])->name('api.getLabDetail');


    Route::get('faqs', ['uses' => 'App\Http\Controllers\API\V1\FaqsController@getFaqs'])->name('api.getFaqs');
    Route::get('sliders', ['uses' => 'App\Http\Controllers\API\V1\SlidersController@getSliders'])->name('api.getSliders');

    $router->group(['prefix' => 'transaction/product'], function () use ($router) {
        $router->post('cart', ['uses' => 'App\Http\Controllers\API\V1\ProductController@storeCart'])->name('api.user.storeCart');
        $router->post('update-cart/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateCart'])->name('api.user.updateCart');
        $router->delete('delete-cart/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@deleteCart'])->name('api.user.deleteCart');
        $router->get('cart', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getCart'])->name('api.user.getCart');
        $router->get('cart-choose-product', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getCartChooseProduct'])->name('api.user.getCartChooseProduct');

        $router->get('cart-receiver', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getReceiver'])->name('api.user.getReceiver');
        $router->post('cart-receiver', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateReceiver'])->name('api.user.updateReceiver');
        $router->get('cart-address', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getAddress'])->name('api.user.getAddress');
        $router->post('cart-address', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateAddress'])->name('api.user.updateAddress');
        $router->get('cart-shipping', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getShipping'])->name('api.user.getShipping');
        $router->post('cart-shipping', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateShipping'])->name('api.user.updateShipping');
        $router->get('cart-summary', ['uses' => 'App\Http\Controllers\API\V1\ProductController@cartSummary'])->name('api.user.cartSummary');
        $router->get('cart-payment', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getPayment'])->name('api.user.getPayment');
        $router->post('cart-checkout', ['uses' => 'App\Http\Controllers\API\V1\ProductController@checkout'])->name('api.user.checkout');

    });

    $router->group(['prefix' => 'transaction/doctor'], function () use ($router) {

        $router->get('/', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@getDoctor'])->name('api.getDoctor');
        $router->get('/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@getDoctorDetail'])->name('api.getDoctorDetail');
        $router->post('/{id}/book', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@bookDoctor'])->name('api.user.bookDoctor');

    });
});

// Non Login

Route::post('login', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@login'])->name('api.login');
Route::get('logout', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@logout'])->name('api.logout');

Route::post('sign-up', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@signUp'])->name('api.signUp');
Route::get('search-klinik', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchKlinik'])->name('api.searchKlinik');
Route::get('search-city', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchCity'])->name('api.searchCity');
Route::get('search-district', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchDistrict'])->name('api.searchDistrict');
Route::get('search-subdistrict', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@searchSubdistrict'])->name('api.searchSubdistrict');

Route::post('forgot-password', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@forgotPassword'])->name('api.forgotPassword');

Route::get('version', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@version'])->name('api.version');
Route::post('version', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@compareVersion'])->name('api.compareVersion');

Route::get('/', function() {
    return LARAVEL_START;
});

