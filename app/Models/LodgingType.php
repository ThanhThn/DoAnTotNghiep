<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LodgingType extends Model
{
    protected $table = 'lodging_types';
    protected $fillable = ['name', 'description'];

    public $timestamps = false;
    protected $hidden = ['created_at', 'updated_at'];

}
