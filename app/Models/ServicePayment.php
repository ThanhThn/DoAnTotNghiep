<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServicePayment extends Model
{
    protected $table = 'service_payments';

    protected $fillable = [
        'id',
        'contract_id',
        'room_service_invoice_id',
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


    protected $casts = [
        'amount_paid' => 'float',
        'payment_amount' => 'float',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id  = Str::uuid();
            }
        });
    }

    public function roomServiceUsage()
    {
        return $this->belongsTo(RoomServiceUsage::class, 'room_service_invoice_id')->with(['service', 'unit']);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class)->with('room');
    }

}
