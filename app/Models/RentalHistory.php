<?php

namespace App\Models;

use Google\Service\HangoutsChat\Resource\Rooms;
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
        'payment_method',
        'due_date'
    ];

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at','updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
