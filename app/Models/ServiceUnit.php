<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUnit extends Model
{
    protected $table = "service_units";
    protected $fillable = [
        'unit_id',
        'service_id',
    ];

    protected $hidden = ['created_at','updated_at'];
    public function unit(){
        return $this->belongsTo(Unit::class);
    }

    public function service(){
        return $this->belongsTo(Service::class);
    }
}
