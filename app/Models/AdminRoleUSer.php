<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRoleUSer extends Model
{
    protected $table = 'admin_role_user';
    protected $fillable = [
        'user_id',
        'role_id'
    ];
}
