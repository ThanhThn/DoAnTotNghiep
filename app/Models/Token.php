<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isEmpty;

class Token extends Model
{
    protected $table = "tokens";
    protected $fillable = [
        'id',
        'token',
        'device',
        'user_id',
        'token_type',
        'token_expired'
    ];

    public $incrementing = false;
    protected $keyType = "string";
    protected $primaryKey = "id";

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
