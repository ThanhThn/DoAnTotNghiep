<?php

namespace App\Services\ChannelMember;

use App\Models\ChannelMember;
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

    function detail($memberId, $memberType, $channelId)
    {
        return ChannelMember::on('pgsqlReplica')->where([
            'member_id' => $memberId,
            'member_type' => $memberType,
            'channel_id' => $channelId
        ])->firstOrFail();
    }
}
