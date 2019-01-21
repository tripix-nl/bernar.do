<?php

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

Route::feeds();
Route::get('/', 'HomeController');
Route::get('about', 'HomeController');
Route::get('/{slug}', 'PostController')->where('slug', '[a-z0-9\-]+');
