<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'id',
        'object_id',
        'object_type',
        'title',
        'body',
        'type',
        'url',
        'is_seen'
    ];

    protected $keyType = "string";
    public $incrementing = false;
    public $primaryKey = 'id';
    protected $hidden = ['updated_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }
}
