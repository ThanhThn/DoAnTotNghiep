<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatHistory extends Model
{
    protected $table = 'chat_histories';
    protected $fillable = [
        'id',
        'channel_id',
        'sender_id',
        'sender_type',
        'context',
        'status'
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
            $model->status = 1;
        });
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

}
