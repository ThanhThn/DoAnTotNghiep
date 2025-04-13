<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = [
        'id',
        'wallet_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'description'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
