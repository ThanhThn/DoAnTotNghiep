<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => 'jwt.verify']);

Route::post('/broadcasting/auth', function (Request $request) {
    $response = Broadcast::auth($request);
})->middleware('jwt.verify');

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('notification-user-{userId}', function ($user, $userId) {
    return $userId == $user->id;
});
