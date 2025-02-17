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
    protected $hidden = ['created_at','updated_at', 'is_enabled'];

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

    public function services()
    {
        return $this->belongsToMany(LodgingService::class, 'room_services', 'room_id', 'lodging_service_id');
    }
}
