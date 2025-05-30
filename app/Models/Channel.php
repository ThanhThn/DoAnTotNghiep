<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Channel extends Model
{
    protected $table = 'channels';
    protected $fillable = [
        'id',
        'room_id'
    ];

    protected $keyType = "string";
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $hidden = ['updated_at'];


    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatHistory::class)->latest();
    }

    public function members()
    {
        return $this->hasMany(ChannelMember::class);
    }

    public function room(){
        return $this->belongsTo(Room::class);
    }
}
