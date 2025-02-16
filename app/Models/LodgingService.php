<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LodgingService extends Model
{
    protected $table = 'lodging_services';

    protected $fillable = [
        'id',
        'service_id',
        'name',
        'lodging_id',
        'unit_id',
        'price_per_unit',
        'is_enabled',
        'payment_date',
        'late_days'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'late_days' => 'integer',
        'is_enabled' => 'boolean',
        'payment_date' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function service(){
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function lodging(){
        return $this->belongsTo(Lodging::class, 'lodging_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
