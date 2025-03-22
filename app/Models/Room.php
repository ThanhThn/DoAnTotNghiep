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
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }

            if ($model->current_tenants > $model->max_tenants) {
                throw new \Exception('Cannot create model: max tenants limit reached.');
            }

            $channel = Channel::create([
                'room_id' => $model->id,
            ]);

            ChannelMember::create([
                'channel_id' => $channel->id,
                'member_id' => $model->lodging_id,
                'member_type' => config('constant.object.type.lodging'),
                'joined_at' => now(),
            ]);
        });
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function services()
    {
        return $this->belongsToMany(LodgingService::class, 'room_services', 'room_id', 'lodging_service_id');
    }

    function roomServices()
    {
        return $this->hasMany(RoomService::class, 'room_id', 'id')->with('lodgingService');
    }

    public function lodging()
    {
        return $this->belongsTo(Lodging::class, 'lodging_id')->with('type');
    }

}
