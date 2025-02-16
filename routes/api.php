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


//Permission
Route::group(['prefix' => 'permission', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('list_by_user', 'PermissionController@listByUser')->middleware('jwt.verify');
});

//Service
Route::group(['prefix' => 'service' , 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('list', 'ServiceController@list');
});

//Unit
Route::group(['prefix' => 'unit' , 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('list', 'UnitController@list');
    Route::get('list_by_service', 'UnitServiceController@unitsByService');
});
