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
        $router->get('address', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@getAddress'])->name('api.user.getAddress');
        $router->post('address', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@updateAddress'])->name('api.user.updateAddress');
        $router->post('verification-email', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@verifEmail'])->name('api.user.verifEmail');
        $router->post('verification-phone', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@verifPhone'])->name('api.user.verifPhone');
        $router->get('notification', ['uses' => 'App\Http\Controllers\API\V1\ProfileController@notifications'])->name('api.user.notifications');
    });

    Route::get('home', ['uses' => 'App\Http\Controllers\API\V1\HomeController@home'])->name('api.home');
    Route::get('', ['uses' => 'App\Http\Controllers\API\V1\HomeController@notifications'])->name('api.notifications');

    Route::get('article', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticle'])->name('api.getArticle');
    Route::get('article/{id}', ['uses' => 'App\Http\Controllers\API\V1\ArticleController@getArticleDetail'])->name('api.getArticleDetail');

    Route::get('product', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getProduct'])->name('api.getProduct');
    Route::get('product/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getProductDetail'])->name('api.getProductDetail');

    Route::get('faqs', ['uses' => 'App\Http\Controllers\API\V1\FaqsController@getFaqs'])->name('api.getFaqs');
    Route::get('sliders', ['uses' => 'App\Http\Controllers\API\V1\SlidersController@getSliders'])->name('api.getSliders');

    $router->group(['prefix' => 'transaction/product'], function () use ($router) {

        $router->get('cart', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getCart'])->name('api.product.getCart');
        $router->post('cart', ['uses' => 'App\Http\Controllers\API\V1\ProductController@storeCart'])->name('api.product.storeCart');
        $router->post('update-cart/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateCart'])->name('api.product.updateCart');
        $router->delete('delete-cart/{id}', ['uses' => 'App\Http\Controllers\API\V1\ProductController@deleteCart'])->name('api.product.deleteCart');
        $router->post('cart-choose-product', ['uses' => 'App\Http\Controllers\API\V1\ProductController@postCartChooseProduct'])->name('api.product.postCartChooseProduct');
        $router->get('cart-receiver', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getReceiver'])->name('api.product.getReceiver');
        $router->post('cart-receiver', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateReceiver'])->name('api.product.updateReceiver');
        $router->get('cart-address', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getAddress'])->name('api.product.getAddress');
        $router->get('cart-shipping', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getShipping'])->name('api.product.getShipping');
        $router->post('cart-shipping', ['uses' => 'App\Http\Controllers\API\V1\ProductController@updateShipping'])->name('api.product.updateShipping');
        $router->get('cart-summary', ['uses' => 'App\Http\Controllers\API\V1\ProductController@cartSummary'])->name('api.product.cartSummary');
        $router->get('cart-payment', ['uses' => 'App\Http\Controllers\API\V1\ProductController@getPayment'])->name('api.product.getPayment');
        $router->post('cart-checkout', ['uses' => 'App\Http\Controllers\API\V1\ProductController@checkout'])->name('api.product.checkout');

    });

    $router->group(['prefix' => 'transaction/doctor'], function () use ($router) {

        $router->get('/', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@getDoctor'])->name('api.doctor.getDoctor');
        $router->get('doctor-category', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@doctorCategory'])->name('api.doctor.doctorCategory');
        $router->get('detail/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@getDoctorDetail'])->name('api.doctor.getDoctorDetail');
        $router->get('list-book/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@listBookDoctor'])->name('api.doctor.listBookDoctor');
        $router->get('check-schedule/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@checkSchedule'])->name('api.doctor.checkSchedule');
        $router->get('schedule-address', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@scheduleAddress'])->name('api.doctor.scheduleAddress');
        $router->get('schedule-summary/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@scheduleSummary'])->name('api.doctor.scheduleSummary');
        $router->get('doctor-payment/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@getPayment'])->name('api.doctor.getPayment');
        $router->post('doctor-checkout/{id}', ['uses' => 'App\Http\Controllers\API\V1\DoctorController@checkout'])->name('api.doctor.checkout');

    });

    $router->group(['prefix' => 'transaction/lab'], function () use ($router) {

        $router->get('/', ['uses' => 'App\Http\Controllers\API\V1\LabController@getLab'])->name('api.lab.getLab');
        $router->get('detail/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@getLabDetail'])->name('api.lab.getLabDetail');
        $router->get('cart', ['uses' => 'App\Http\Controllers\API\V1\LabController@getCart'])->name('api.lab.getCart');
        $router->post('cart', ['uses' => 'App\Http\Controllers\API\V1\LabController@storeCart'])->name('api.lab.storeCart');
        $router->delete('cart/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@deleteCart'])->name('api.lab.deleteCart');
        $router->post('choose-cart', ['uses' => 'App\Http\Controllers\API\V1\LabController@chooseCart'])->name('api.lab.chooseCart');
        $router->get('list-book', ['uses' => 'App\Http\Controllers\API\V1\LabController@listBookLab'])->name('api.lab.listBookLab');
        $router->get('schedule-address', ['uses' => 'App\Http\Controllers\API\V1\LabController@scheduleAddress'])->name('api.lab.scheduleAddress');
        $router->get('schedule-summary/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@scheduleSummary'])->name('api.lab.scheduleSummary');
        $router->get('lab-payment/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@getPayment'])->name('api.lab.getPayment');
        $router->post('lab-checkout/{id}', ['uses' => 'App\Http\Controllers\API\V1\LabController@checkout'])->name('api.lab.checkout');

    });

   $router->group(['prefix' => 'transaction/history'], function () use ($router) {

       $router->get('/', ['uses' => 'App\Http\Controllers\API\V1\HistoryController@index'])->name('api.transaction.index');
       $router->get('detail/{id}', ['uses' => 'App\Http\Controllers\API\V1\HistoryController@detail'])->name('api.transaction.detail');

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

Route::get('redirect-to-app', ['uses' => 'App\Http\Controllers\API\V1\GeneralController@redirectApps'])->name('api.redirectApps');

Route::get('/', function() {
    return LARAVEL_START;
});

