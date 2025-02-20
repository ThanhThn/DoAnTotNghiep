<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $table = "roles";
    protected $fillable = [
        'id',
        'name',
        'description',
        'lodging_id'
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
