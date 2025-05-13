<?php

namespace App\Services\ChannelMember;

use App\Models\ChannelMember;
use App\Models\Lodging;
use App\Services\Channel\ChannelService;

class ChannelMemberService
{
    static function isMemberOfChannel($memberId, $memberType, $channelId)
    {
        return ChannelMember::on('pgsqlReplica')->where([
            'member_id' => $memberId,
            'member_type' => $memberType,
            'channel_id' => $channelId
        ])->exists();
    }

    static function isUserOrLodgingInChannel($userId, $channelId)
    {
        if (self::isMemberOfChannel($userId, config('constant.object.type.user'), $channelId)) {
            return true;
        }

        $lodgingIds = Lodging::where('user_id', $userId)->pluck('id')->toArray();
        return ChannelMember::whereIn('member_id', $lodgingIds)->where([
            'channel_id' => $channelId,
            'member_type' => config('constant.object.type.lodging')
        ])->exists();
    }

    function detail($memberId, $memberType, $channelId)
    {
        return ChannelMember::on('pgsqlReplica')->where([
            'member_id' => $memberId,
            'member_type' => $memberType,
            'channel_id' => $channelId
        ])->firstOrFail();
    }
}
