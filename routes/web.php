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

Route::get('/', function () {
    return view('login');
});

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout');

Route::group(['middleware' => ['auth']], function() {
	
	Route::delete('services/delete/{service}', 'ServiceController@delete');
	Route::post('services/update/{service}', 'ServiceController@update');
	Route::post('services/store', 'ServiceController@store');
	Route::get('services/edit/{service}', 'ServiceController@edit');
	Route::get('services/create', 'ServiceController@create');
	Route::get('services/export', 'ServiceController@Download');
	Route::get('services/grid', 'ServiceController@Grid');
	Route::get('services', 'ServiceController@index');
	
	Route::get('user_services/export', 'HomeController@userServicesDownload');
	Route::get('user_services/grid', 'HomeController@userServicesGrid');
	Route::get('user_services', 'HomeController@userServices');
	 
	
});