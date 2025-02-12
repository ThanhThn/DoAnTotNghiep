<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lodging extends Model
{
    protected $table = 'lodgings';

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
