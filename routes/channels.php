<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Broadcast::routes(['middleware' => ['jwt.verify']]);

Route::post('/broadcasting/auth', function (Request $request) {
    Broadcast::auth($request);
})->middleware(['jwt.verify']);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('private:notification', function ($user) {
    return true;
});
