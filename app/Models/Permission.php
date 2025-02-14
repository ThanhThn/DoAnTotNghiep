<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = [
        'id',
        'name',
        'description',
        'type',
        'end_point'
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
