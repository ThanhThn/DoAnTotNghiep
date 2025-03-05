<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalHistory extends Model
{
    protected $table = 'rental_histories';

    protected $fillable = [
        'id',
        'contract_id',
        'payment_amount',
        'amount_paid',
        'status',
        'payment_date',
        'last_payment_date',
        'payment_method'
    ];

    public $incrementing = false;
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }
}
