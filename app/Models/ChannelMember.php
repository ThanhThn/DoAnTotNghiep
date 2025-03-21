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
        'user_id',
        'role',
        'joined_at'
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
}
