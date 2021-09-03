<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => env('ADMIN_URL'), 'middleware' => ['web']], function () use ($router) {

    $router->get('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@getLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login');
    $router->post('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@postLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login.post');
    $router->get('logout', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@doLogout'])->name('admin.logout');

    $router->group(['middleware' => ['adminLogin', 'preventBackHistory']], function () use ($router) {

        $router->group(['prefix' => 'profile'], function () use ($router) {
            $router->get('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getProfile'])->name('admin.get_profile');
            $router->post('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postProfile'])->name('admin.post_profile');
            $router->get('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getPassword'])->name('admin.get_password');
            $router->post('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postPassword'])->name('admin.post_password');
            $router->get('/', ['uses'=>'App\Http\Controllers\Admin\ProfileController@profile'])->name('admin.profile');
        });

        $router->group(['middleware' => ['adminAccessPermission']], function () use ($router) {
            $listRouter = [
                'App\Http\Controllers\Admin\SettingsController' => 'settings',
                'App\Http\Controllers\Admin\AdminController' => 'admin',
                'App\Http\Controllers\Admin\RoleController' => 'role',
                'App\Http\Controllers\Admin\UserController' => 'user',
                'App\Http\Controllers\Admin\ArticleController' => 'article',
                'App\Http\Controllers\Admin\ArticleCategoryController' => 'article-category',
                'App\Http\Controllers\Admin\FaqsController' => 'faqs',
                'App\Http\Controllers\Admin\KlinikController' => 'klinik',
                'App\Http\Controllers\Admin\ProductController' => 'product',
                'App\Http\Controllers\Admin\LabController' => 'lab',
            ];

            foreach ($listRouter as $controller => $linkName) {
                $router->get($linkName . '/data', $controller . '@dataTable')->name('admin.' . $linkName . '.dataTable');
                $router->resource($linkName, $controller, ['as' => 'admin']);
            }

        });

        $router->get('/', ['uses' => 'App\Http\Controllers\Admin\DashboardController@dashboard'])->name('admin');

    });
});

Route::get('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@changeTokenPassword'])->name('web.changeTokenPassword');
Route::post('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@updatePassword'])->name('web.updatePassword');

Route::get('confirm-email', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@confirmEmail'])->name('web.confirmEmail');
Route::get('confirm-phone', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@confirmPhone'])->name('web.confirmPhone');

Route::group(['prefix' => env('ADMIN_URL'), 'middleware' => ['web']], function () use ($router) {
    $router->get('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@getLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login');
    $router->post('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@postLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login.post');
    $router->get('logout', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@doLogout'])->name('admin.logout');

    $router->group(['middleware' => ['adminLogin', 'preventBackHistory']], function () use ($router) {

        $router->group(['prefix' => 'profile'], function () use ($router) {
            $router->get('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getProfile'])->name('admin.get_profile');
            $router->post('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postProfile'])->name('admin.post_profile');
            $router->get('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getPassword'])->name('admin.get_password');
            $router->post('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postPassword'])->name('admin.post_password');
            $router->get('/', ['uses'=>'App\Http\Controllers\Admin\ProfileController@profile'])->name('admin.profile');
        });

        $router->group(['middleware' => ['adminAccessPermission']], function () use ($router) {
            $listRouter = [
                'App\Http\Controllers\Admin\SettingsController' => 'settings',
                'App\Http\Controllers\Admin\AdminController' => 'admin',
                'App\Http\Controllers\Admin\RoleController' => 'role',
                'App\Http\Controllers\Admin\PageController' => 'page',
                'App\Http\Controllers\Admin\HowToPlayController' => 'how-to-play',

                'App\Http\Controllers\Admin\V1\QuestionsController' => 'v1-questions',
                'App\Http\Controllers\Admin\V1\UsersController' => 'v1-users',
                'App\Http\Controllers\Admin\V1\LogLoginController' => 'v1-log-login',
                'App\Http\Controllers\Admin\V1\GamesController' => 'v1-games',
                'App\Http\Controllers\Admin\V1\MantraController' => 'v1-mantra',

            ];

            foreach ($listRouter as $controller => $linkName) {
                $router->get($linkName . '/data', $controller . '@dataTable')->name('admin.' . $linkName . '.dataTable');
                $router->resource($linkName, $controller, ['as' => 'admin']);
            }

        });

        $router->group(['prefix' => 'v1-questions/{parent_id}'], function () use ($router) {
            $router->get('v1-question-details/data', 'App\Http\Controllers\Admin\V1\QuestionDetailsController@dataTable')->name('admin.v1-question-details.dataTable');
            $router->resource('v1-question-details', 'App\Http\Controllers\Admin\V1\QuestionDetailsController', ['as' => 'admin']);
        });

        $router->get('/', ['uses' => 'App\Http\Controllers\Admin\DashboardController@dashboard'])->name('admin');

    });

});

Route::get('/', function () {
    return view('welcome');
});
