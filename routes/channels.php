<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::post('/broadcasting/auth', function (Request $request) {
    Log::info('Auth request received', [
        'headers' => $request->headers->all(),
        'body' => $request->all(),
        'user' => auth()->user()
    ]);
    $response = Broadcast::auth($request);
    Log::info('Auth response sent', ['response' => $response]);
    return $response;
})->middleware('jwt.verify');

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('', function ($user) {
    return true;
});

Broadcast::channel('notification', function ($user) {
    return true;
});
