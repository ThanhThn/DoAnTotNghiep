<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Auth\Admin'], function ($route) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout')->middleware('jwt_admin.verify');
    Route::get('refresh', 'AuthController@refresh');

    Route::post("request_otp", "AuthController@requestOTP");
    Route::post("verify_otp", "AuthController@verifyOTP");
    Route::post("reset_password", "AuthController@resetPassword");
});

Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => 'jwt_admin.verify'], function ($route) {

    Route::post('list', 'UserController@listUserForAdmin');
    Route::post('create', 'UserController@create');
    Route::get('detail/{userId}', 'UserController@detail');
    Route::post('update', 'UserController@update');
    Route::delete('delete/{userId}', 'UserController@delete');
});

Route::group(['prefix' => 'lodging', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => 'jwt_admin.verify'], function ($route) {

    Route::post('list', 'LodgingController@list');
    Route::post('create', 'LodgingController@create');
    Route::get('detail/{lodgingId}', 'LodgingController@detail');
    Route::post('update', 'LodgingController@update');
    Route::delete('delete/{lodgingId}', 'LodgingController@delete');
    Route::put('restore/{lodgingId}', 'LodgingController@restore');
});

Route::group(['prefix' => 'dashboard', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => 'jwt_admin.verify'], function ($route) {

    Route::post('overview', 'DashboardController@overview');
});
