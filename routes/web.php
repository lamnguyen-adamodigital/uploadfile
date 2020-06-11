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



Route::group(['namespace' => 'Web'], function () {
    Route::get('/', 'UploadController@viewImage')->name('view');
    Route::get('/upload', 'UploadController@viewImage')->name('view');
    route::post('/upload', 'UploadController@uploadImage')->name('admin.upload');
});
