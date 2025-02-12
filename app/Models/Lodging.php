<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lodging extends Model
{
    protected $table = 'lodgings';

    protected $fillable = [
        'id',
        'name',
        'user_id',
        'address',
        'province_id',
        'district_id',
        'ward_id',
        'latitude',
        'longitude',
        'type_id',
        'is_enabled',
        'payment_date',
        'late_days',
        'area_room_default',
        'price_room_default',
        'phone_contact',
        'email_contact',
    ];

    protected $keyType = "string";
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';


    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'area_room_default' => 'float',
        'price_room_default' => 'decimal'
    ];
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
