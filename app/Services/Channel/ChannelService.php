<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\ChannelMember;

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

        $query = $query->with(['latestMessage.sender', 'room.lodging'
        ])->offset($offset)->limit($limit)->get()->sortByDesc(function ($channel) {
                return $channel->latest_message->created_at ?? $channel->created_at;
            })->values();
//            ->map(function ($channel) use ($memberId) {
//                $channel->viewed = !Interaction::on("pgsqlReplica")
//                    ->where('object_id_a', $channel->id)
//                    ->where('object_id_b', $memberId)
//                    ->where('interaction_type', config('constants.interaction.unread'))
//                    ->exists();
//                return $channel;
//            })

        return [
            'total' => $total,
            'data'  => $query
        ];
    }
}
