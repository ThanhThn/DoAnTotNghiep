<?php

namespace App\Services\Channel;

use App\Models\Channel;

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
        $query = Channel::whereHas('members', function ($query) use ($memberId, $memberType) {
            $query->where(['member_id' => $memberId, 'member_type' => $memberType]);
        })
            ->with([
                'latestMessage', 'room.lodging'
            ])
            ->get();

        $total = $query->count();

        $query = $query->sortByDesc(function ($channel) {
                return $channel->latest_message->created_at ?? null;
            })
            ->slice($offset, $limit)
//            ->map(function ($channel) use ($memberId) {
//                $channel->viewed = !Interaction::on("pgsqlReplica")
//                    ->where('object_id_a', $channel->id)
//                    ->where('object_id_b', $memberId)
//                    ->where('interaction_type', config('constants.interaction.unread'))
//                    ->exists();
//                return $channel;
//            })
            ->values()->all();

        return [
            'total' => $total,
            'data'  => $query
        ];
    }
}
