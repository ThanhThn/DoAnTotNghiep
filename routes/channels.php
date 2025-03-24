<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Lodging\LodgingService;
use App\Services\ChannelMember\ChannelMemberService;

Route::post('/broadcasting/auth', function (Request $request) {
    $response = Broadcast::auth($request);
})->middleware('jwt.verify');

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notification.user.{userId}', function ($user, $userId) {
    return $userId == $user->id;
});

Broadcast::channel('notification.lodging.{lodgingId}', function ($user, $lodgingId) {
    return true;
});

Broadcast::channel('feedback.{objectType}.{objectId}', function ($user, $objectType, $objectId) {
    return match ($objectType) {
        config('constant.object.type.user') => $objectId == $user->id,
        config('constant.object.type.lodging') => LodgingService::isOwnerLodging($objectId, $user->id),
        default => false,
    };
});

Broadcast::channel('chat.{channelId}', function ($user, $channelId) {
    return ChannelMemberService::isUserOrLodgingInChannel($user->id, $channelId);
});
