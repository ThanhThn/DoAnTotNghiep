<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lodging extends Model
{
    use SoftDeletes;
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

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'area_room_default' => 'float',
        'price_room_default' => 'decimal:2',
        'late_days' => 'integer',
        'payment_date' => 'integer',
        'is_enabled' => 'boolean',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $dates = ['deleted_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });

    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district(){
        return $this->belongsTo(District::class, 'district_id');
    }

    public function ward(){
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function rooms(){
        return $this->hasMany(Room::class, 'lodging_id');
    }

    public function type()
    {
        return $this->belongsTo(LodgingType::class, 'type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'full_name', 'email', 'phone']);
    }
}
