<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChannelMember extends Model
{
    protected $table = 'channel_members';
    protected $fillable = [
        'id',
        'channel_id',
        'member_id',
        'member_type',
        'joined_at',
        'last_left_at'
    ];

    protected $keyType = "string";
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $hidden = ['created_at','updated_at'];


    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class, 'channel_id', 'channel_id');
    }
}
