<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\ChannelMember;
use Illuminate\Support\Facades\Redis;

class ChannelService
{

    private $_memberId;
    private $_memberType;

    function __construct($memberId, $memberType){
        $this->_memberId = $memberId;
        $this->_memberType = $memberType;
    }

    public function list($limit = 2, $offset = 0)
    {
        $memberId = $this->_memberId;
        $memberType = $this->_memberType;
        // Lấy danh sách các channel mà member đã tham gia
        $query = Channel::on('pgsqlReplica')->whereHas('members', function ($query) use ($memberId, $memberType) {
            $query->where(['member_id' => $memberId, 'member_type' => $memberType]);
        });

        $total = $query->count();

        $query = $query->leftJoin('chat_histories', function ($join) {
            $join->on('channels.id', '=', 'chat_histories.channel_id')
                ->where('chat_histories.created_at', function ($subQuery) {
                    $subQuery->selectRaw('MAX(created_at)')
                        ->from('chat_histories as ch2')
                        ->whereColumn('ch2.channel_id', 'channels.id');
                });
        })
            ->leftJoin('channel_members', function ($join) use ($memberId, $memberType) {
                $join->on('channels.id', '=', 'channel_members.channel_id')
                    ->where(['channel_members.member_id' => $memberId, 'channel_members.member_type' => $memberType]);
            })
            ->select('channels.*', 'channel_members.joined_at')
            ->with(['latestMessage' => function ($query) use ($memberId, $memberType) {
                $query->whereExists(function ($subQuery) use ($memberId, $memberType) {
                    $subQuery->selectRaw(1)
                        ->from('channel_members')
                        ->whereColumn('channel_members.channel_id', 'chat_histories.channel_id')
                        ->where(['member_id' => $memberId, 'member_type' => $memberType])
                        ->whereColumn('chat_histories.created_at', '>=', 'channel_members.joined_at');
                })->with('sender');
            }, 'room.lodging'])
            ->orderByRaw('COALESCE(
    CASE WHEN channel_members.joined_at > chat_histories.created_at THEN NULL
         ELSE chat_histories.created_at
    END,
    channel_members.joined_at
) DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Check Redis first before calculating read status
        $query = $query->map(function ($channel) use ($memberId) {
            // Key Redis: user:{memberId}:channel:{channelId}:lastLeftAt
            $redisKey = "$memberId:channel:{$channel->id}:lastLeftAt";

            // Try to get 'lastLeftAt' from Redis
            $lastLeftAt = Redis::get($redisKey);

            // If Redis doesn't have it, fetch from DB and update Redis
            if (!$lastLeftAt) {
                $lastLeftAt = ChannelMember::on('pgsqlReplica')
                    ->where('member_id', $memberId)
                    ->where('channel_id', $channel->id)
                    ->value('last_left_at');

            }

            // Check if user has read the last message based on 'lastLeftAt'
            $latestMessageTime = optional($channel->latestMessage)->created_at;

            $channel->is_read = false;
            if ($latestMessageTime && $lastLeftAt) {
                $channel->is_read = $latestMessageTime <= $lastLeftAt;
            }

            return $channel;
        });

        return [
            'total' => $total,
            'data'  => $query
        ];
    }

    public function leaveChannel($channelId)
    {
        $key = $this->_memberId . ":channel:" . $channelId . ":lastLeftAt";
        $timestamp = now()->timestamp;

        Redis::set($key, $timestamp);

        Redis::expire($key, 24 * 60 * 60); // 5 phút
        return true;
    }
}
