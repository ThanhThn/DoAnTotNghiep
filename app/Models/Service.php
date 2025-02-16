<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = "services";
    protected $fillable = [
        'id',
        'name'
    ];

    protected $hidden = ['created_at','updated_at'];
}
