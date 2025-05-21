<?php

namespace App\Models;

use Google\Service\HangoutsChat\Resource\Rooms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentPayment extends Model
{
    protected $table = 'rent_payments';

    protected $fillable = [
        'id',
        'contract_id',
        'payment_amount',
        'amount_paid',
        'status',
        'payment_date',
        'last_payment_date',
        'payment_method',
        'due_date',
        'room_rent_invoice_id'
    ];

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at','updated_at'];

    protected $casts = [
        'amount_paid' => 'float',
        'payment_amount' => 'float',
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function roomRentInvoice(){
        return $this->belongsTo(RoomRentInvoice::class);
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class)->with('room');
    }
}
