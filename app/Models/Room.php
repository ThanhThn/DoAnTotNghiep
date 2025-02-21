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
      'payment_date',
      'late_days',
    ];
    protected $hidden = ['created_at','updated_at', 'is_enabled'];

    protected $casts = [
        'area' => 'float',
        'price' => 'decimal:2',
        'late_days' => 'integer',
        'is_enabled' => 'boolean',
        'payment_date' => 'integer',
        'priority' => 'array'
    ];

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

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'room_id', 'id');
    }

    public function services()
    {
        return $this->belongsToMany(LodgingService::class, 'room_services', 'room_id', 'lodging_service_id');
    }

    function roomServices()
    {
        return $this->hasMany(RoomService::class, 'room_id', 'id');
    }

}
