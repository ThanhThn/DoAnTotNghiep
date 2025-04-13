<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentHistory extends Model
{
    protected $table = 'payment_histories';

    protected $fillable = [
        'id',
        'object_id',
        'object_type',
        'room_id',
        'lodging_id',
        'contract_id',
        'amount',
        'payment_method',
        'paid_at',
        'note'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
