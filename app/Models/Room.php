<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
      'id',
      'room_code',
      'lodging_id',
      'price',
      'status',
      'is_enabled',
      'area',
      'priority',
      'current_tenants',
      'max_tenants',
    ];
    protected $hidden = ['created_at','updated_at'];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }
}
