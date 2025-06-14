<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use softDeletes;
    protected $table = "units";
    protected $fillable = [
        'id',
        'name',
        'description',
        'is_fixed'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
