<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $fillable = [
        'id',
        'user_id',
        'room_id',
        'start_date',
        'end_date',
        'remain_amount',
        'deposit_amount',
        'monthly_rent',
        'status',
        'lease_duration',
        'quantity',
        'full_name',
        'phone',
        'gender',
        'address',
        'identity_card',
        'date_of_birth',
        'relatives'
    ];

    protected $keyType = "string";
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $dateFormat = 'Y-m-d H:i:s';
    protected $casts = [
      'relatives' => 'array',
      'lease_duration' => 'integer',
      'quantity' => 'integer',
      'full_name' => 'string',
      'phone' => 'string',
      'gender' => 'boolean',
      'address' => 'string',
      'identity_card' => 'string',

      'remain_amount' => 'decimal:2',
      'monthly_rent' => 'decimal:2',
      'status' => 'integer',
    ];

    protected $hidden = ['created_at','updated_at'];

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
