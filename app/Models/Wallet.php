<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $fillable = [
        'id',
        'object_id',
        'object_type',
        'balance',
        'status'
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

    public function walletable()
    {
        return $this->morphTo(null, 'object_type', 'object_id');
    }
}
