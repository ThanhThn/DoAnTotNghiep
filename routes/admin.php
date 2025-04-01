<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Auth\Admin'], function ($route) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout')->middleware('jwt.verify');
    Route::get('refresh', 'AuthController@refresh');
});

Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => 'jwt_admin.verify'], function ($route) {

    Route::post('list', 'UserController@listUserForAdmin');
    Route::post('create', 'UserController@create');
    Route::get('detail/{userId}', 'UserController@detail');
    Route::post('update', 'UserController@update');
    Route::delete('delete/{userId}', 'UserController@delete');
});
