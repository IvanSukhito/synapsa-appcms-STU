<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@changeTokenPassword'])->name('web.changeTokenPassword');
Route::post('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@updatePassword'])->name('web.updatePassword');

Route::get('/', function () {
    return view('error');
});
