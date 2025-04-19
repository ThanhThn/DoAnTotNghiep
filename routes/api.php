<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;


Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Auth\User'], function ($route) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout')->middleware('jwt.verify');
    Route::get('refresh', 'AuthController@refresh');

    Route::post("request_otp", "AuthController@requestOTP");
    Route::post("verify_otp", "AuthController@verifyOTP");
    Route::post("reset_password", "AuthController@resetPassword");
});

// User
Route::group(['prefix' => 'user', 'namespace' => 'App\Http\Controllers', 'middleware' => ['jwt.verify']], function ($route) {
    Route::get('info', 'UserController@info');
    Route::post('update', 'UserController@update');
    Route::post('change_password', 'UserController@changePassword');

    Route::group(['prefix' => 'client'], function ($route) {
        Route::get('list_lodging_and_rooms', 'ClientController@listLodgingAndRooms');
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
    Route::get('detail/{lodgingId}', 'LodgingController@detail');
    Route::post('update', 'LodgingController@update')->middleware('jwt.verify');
    Route::get('delete/{lodgingId}', 'LodgingController@softDelete')->middleware('jwt.verify');

    Route::post('overview', 'LodgingController@overview')->middleware('jwt.verify');
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

    Route::get('list', 'LodgingServiceController@list');
    Route::delete('delete', 'LodgingServiceController@delete')->middleware('jwt.verify');

    Route::get('list_by_room', 'LodgingServiceController@listByRoom');
});

//Room
Route::group(['prefix' => 'room', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'RoomController@create')->middleware('jwt.verify');
    Route::get('list/{lodgingId}', 'RoomController@listByLodging');
    Route::post('filter', 'RoomController@filter');
    Route::get('detail/{id}', 'RoomController@detail');
    Route::post('update', 'RoomController@update')->middleware('jwt.verify');

    Route::delete('delete', 'RoomController@delete')->middleware('jwt.verify');
});

//Contract
Route::group(['prefix' => 'contract', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('create', 'ContractController@create')->middleware('jwt.verify');

    Route::post('list', 'ContractController@list')->middleware('jwt.verify');

    Route::get('detail/{contractId}', 'ContractController@detail')->middleware('jwt.verify');

    Route::post('update', 'ContractController@update')->middleware('jwt.verify');

    Route::get('debt/{contractId}', 'ContractController@debt');

    Route::post('create_final_bill', 'ContractController@createFinalBill')->middleware('jwt.verify');

    Route::post('end_contract', 'ContractController@endContract')->middleware('jwt.verify');

    Route::post('pay_amount', 'ContractController@paymentAmountByContract' )->middleware('jwt.verify');

    Route::post('list_by_user', 'ContractController@listByUser')->middleware('jwt.verify');
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

    Route::get('{notificationId}/toggle_read',  'NotificationController@toggleRead');
});


Route::group(['prefix' => 'equipment', 'namespace' => 'App\Http\Controllers'] , function ($route) {
    Route::post('create', 'EquipmentController@create')->middleware('jwt.verify');


    Route::get('detail/{equipmentId}', 'EquipmentController@detail');
    Route::post('update','EquipmentController@update')->middleware('jwt.verify');

    Route::get('list', 'EquipmentController@listByLodging');

    Route::delete('delete', 'EquipmentController@delete')->middleware('jwt.verify');
});


// Rental
Route::group(['prefix' => 'rental_history', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list', 'RentalHistoryController@listRentalHistory')->middleware('jwt.verify');

    Route::get('detail/{rentalHistoryId}', 'RentalHistoryController@detailRentalHistory')->middleware('jwt.verify');
});


// RoomUsage
Route::group(['prefix' => 'room_usage', 'namespace' => 'App\Http\Controllers'], function ($route) {
   Route::get('list_need_close', 'RoomUsageController@listUsageNeedCloseByLodging')->middleware('jwt.verify');

   Route::post('close_room_usage', 'RoomUsageController@closeRoomUsage')->middleware('jwt.verify');
});


Route::group(['prefix' => 'service_payment', 'namespace' => 'App\Http\Controllers', 'middleware' => 'jwt.verify'], function ($route) {
    Route::post('list', 'ServicePaymentController@list');

    Route::get('detail/{servicePaymentId}', 'ServicePaymentController@detail')->middleware('jwt.verify');
});

Route::group(['prefix' =>  'channel', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list', 'ChannelController@list')->middleware('jwt.verify');
});

Route::group(['prefix' => 'chat', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list', 'ChatHistoryController@list')->middleware('jwt.verify');

    Route::post('create', 'ChatHistoryController@create')->middleware('jwt.verify');
});

Route::group(['prefix' => 'payment', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('payment_by_contract', 'PaymentController@paymentByContract')->middleware('jwt.verify');

    Route::post('payment_by_user', 'PaymentController@paymentByUser')->middleware('jwt.verify');
});

Route::group(['prefix' => 'wallet', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::get('detail/{walletId}', 'WalletController@detail')->middleware('jwt.verify');
});

Route::group(['prefix' => 'transaction', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list_by_wallet', 'TransactionController@listByWallet')->middleware('jwt.verify');
});

Route::group(['prefix' => 'payment_history', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list', 'PaymentHistoryController@list')->middleware('jwt.verify');
});


Route::group(['prefix' => 'invoice', 'namespace' => 'App\Http\Controllers'], function ($route) {
    Route::post('list', 'InvoiceController@list')->middleware('jwt.verify');

    Route::post('detail', 'InvoiceController@detail')->middleware('jwt.verify');
});
