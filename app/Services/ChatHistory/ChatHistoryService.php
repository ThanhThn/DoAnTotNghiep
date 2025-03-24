<?php

namespace App\Services\ChatHistory;

use App\Events\ChatEvent;
use App\Models\Channel;
use App\Models\ChannelMember;
use App\Models\ChatHistory;
use App\Models\Lodging;
use App\Models\User;
use App\Services\ChannelMember\ChannelMemberService;
use Carbon\Carbon;

class ChatHistoryService
{
    private $_memberId;
    private $_memberType;

    function __construct($memberId, $memberType){
        $this->_memberId = $memberId;
        $this->_memberType = $memberType;
    }

    public function list($channelId, $limit = 2, $offset = 0)
    {
        $memberId = $this->_memberId;
        $memberType = $this->_memberType;

        $channelMember = (new ChannelMemberService())->detail($memberId, $memberType, $channelId);

        $query = ChatHistory::on('pgsqlReplica')->with([
            'sender'])->where('channel_id', $channelId)->where('created_at', ">=", $channelMember->joined_at);
        $total = $query->count();
        $query = $query->orderBy('created_at', 'desc')->offset($offset)->limit($limit)->get();

        return [
            'total' => $total,
            'data'  => $query
        ];
    }


    public function create($channelId, $message){
        $chat = ChatHistory::create([
            'channel_id' => $channelId,
            'sender_id' => $this->_memberId,
            'sender_type' => $this->_memberType,
            'content' => [
                'text' => $message
            ],
        ]);

        event(new ChatEvent('new', $chat));

        return $chat;
    }
}
