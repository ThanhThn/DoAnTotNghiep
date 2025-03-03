<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('private:notification', function ($user) {
    Log::info('Channel auth', ['user' => $user]);
    return true;
});
