<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lodging extends Model
{
    protected $table = 'lodgings';

    protected $fillable = [
        'id', // Đảm bảo 'id' có trong fillable để Laravel có thể xử lý đúng
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

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'area_room_default' => 'float',
        'price_room_default' => 'decimal:2',
        'late_days' => 'integer',
        'is_enabled' => 'boolean',
        'payment_date' => 'integer',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }
}
