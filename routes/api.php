<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers\Auth\User'], function ($route) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
});

// User
Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers', 'middleware' => ['jwt.verify']], function ($route) {
    Route::get('info', 'UserController@info');
    Route::post('update', 'UserController@update');
});

//General
Route::group(['prefix' => 'general', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('provinces', 'GeneralController@listProvince');
    Route::get('districts', 'GeneralController@listDistrict');
    Route::get('wards', 'GeneralController@listWard');
});


//Lodging
Route::group(['prefix' => 'lodging', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('list_by_user', 'LodgingController@listByUser')->middleware('jwt.verify');
    Route::post('create', 'LodgingController@create')->middleware('jwt.verify');
});

//Lodging type
Route::group(['prefix' => 'lodging_type', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('list', 'LodgingTypeController@list');
});
