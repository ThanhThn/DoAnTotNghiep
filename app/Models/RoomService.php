<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RoomService extends Model
{
    protected $table = 'room_services';
    protected $fillable = [
        'id',
        'lodging_service_id',
        'room_id',
        'last_recorded_value',
        'is_enabled'
    ];

    protected $hidden = ['created_at','updated_at'];
    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = "id";


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function lodgingService()
    {
        return $this->belongsTo(LodgingService::class, 'lodging_service_id')->with(['service', 'unit']);
    }

    public function service()
    {
        return $this->hasOneThrough(Service::class, LodgingService::class, 'id', 'id', 'lodging_service_id', 'service_id');
    }
}
