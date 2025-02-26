<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Feedback extends Model
{
    protected $table = "feedbacks";

    protected $fillable = [
        'id',
        'room_id',
        'lodging_id',
        'title',
        'body',
        'status',
        'user_id'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'body' => 'array'
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }
}

