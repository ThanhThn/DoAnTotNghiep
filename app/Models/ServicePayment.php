<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServicePayment extends Model
{
    protected $table = 'service_payments';

    protected $fillable = [
        'contract_id',
        'room_service_usage_id',
        'payment_amount',
        'amount_paid',
        'payment_date',
        'last_payment_date',
        'due_date',
        'payment_method'
    ];

    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = false;

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id  = Str::uuid();
            }
        });
    }
}
