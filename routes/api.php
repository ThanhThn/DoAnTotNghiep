<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;


Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers\Auth\User'], function ($route) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
});

// User
Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers', 'middleware' => ['jwt.verify']], function ($route) {
    Route::get('info', 'UserController@info');
    Route::post('update', 'UserController@update');

    Route::group(['prefix' => 'client'], function ($route) {
        Route::get('list_lodging_and_rooms', 'ClientController@listLodgingAndRooms')->middleware('jwt.verify');
    });
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


//LodgingService
Route::group(['prefix' => 'lodging_service' , 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'LodgingServiceController@create')->middleware('jwt.verify');
    Route::get('list/{lodgingId}', 'LodgingServiceController@listByLodging')->middleware('jwt.verify');
    Route::get('detail/{id}', 'LodgingServiceController@detail');
    Route::post('update', 'LodgingServiceController@update')->middleware('jwt.verify');
});

//Room
Route::group(['prefix' => 'room', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'RoomController@create')->middleware('jwt.verify');
    Route::get('list/{lodgingId}', 'RoomController@listByLodging');
    Route::post('filter', 'RoomController@filter');
    Route::get('detail/{id}', 'RoomController@detail');
    Route::post('update', 'RoomController@update')->middleware('jwt.verify');
});

//Contract
Route::group(['prefix' => 'contract', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'ContractController@create')->middleware('jwt.verify');

    Route::post('list', 'ContractController@list')->middleware('jwt.verify');
});

//Feedback
Route::group(['prefix' => 'feedback', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'FeedbackController@create')->middleware('jwt.verify');
    Route::post('list', 'FeedbackController@list');
    Route::get('list_by_user', 'FeedbackController@listByUser')->middleware('jwt.verify');

    Route::get('detail/{feedbackId}', 'FeedbackController@detail');
    Route::post('update_status', 'FeedbackController@updateStatus')->middleware('jwt.verify');
});

Route::group(['prefix' => 'notification', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'NotificationController@index')->middleware('jwt.verify');

    Route::post('list', 'NotificationController@list')->middleware('jwt.verify');
});


Route::group(['prefix' => 'equipment', 'namespace' => 'App\Http\Controllers'] , function ($route) {
    Route::post('create', 'EquipmentController@create')->middleware('jwt.verify');

    Route::get('list', 'EquipmentController@listByLodging');
});
