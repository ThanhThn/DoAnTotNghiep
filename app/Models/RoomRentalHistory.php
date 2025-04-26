<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RoomRentalHistory extends Model
{
    protected $table = 'room_rent_invoices';

    protected $fillable = [
        'id',
        'room_id',
        'total_price',
        'amount_paid',
        'finalized',
        'month_billing',
        'year_billing',
        'is_finalized_early'
    ];

    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = false;

    protected $casts = [
        'amount_paid' => 'float',
        'total_price' => 'float',
    ];

    protected $hidden = ['created_at','updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id  = Str::uuid();
            }
        });

    }

    public function room() {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function rentalHistories()
    {
        return $this->hasMany(RentPayment::class, 'room_rent_invoice_id')->with('contract');
    }
}
