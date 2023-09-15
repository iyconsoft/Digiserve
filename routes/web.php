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
	
	Route::get('ussd/export', 'HomeController@ussdDownload');
	Route::get('ussd/grid', 'HomeController@ussdGrid');
	Route::get('ussd', 'HomeController@ussd');
	
	Route::get('payment/export', 'HomeController@paymentDownload');
	Route::get('payment/grid', 'HomeController@paymentGrid');
	Route::get('payment', 'HomeController@payment');
	 
	
});