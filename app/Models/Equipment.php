<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'id',
        'name',
        'thumbnail',
        'quantity',
        'type',
        'description',
        'remaining_quantity',
        'lodging_id'
    ];

    protected $primaryKey = 'id';
    protected $keyType = "string";
    public $incrementing = false;

    protected $hidden = ['created_at','updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id = Str::uuid();
            }
        });
    }

    public function roomSetups()
    {
        return $this->hasMany(RoomSetup::class);
    }
}
