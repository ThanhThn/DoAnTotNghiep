<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';
    protected $fillable = [
        'user_id', 'permission_id', 'lodging_id'
    ];

    public function permission(){
        return $this->belongsTo(Permission::class);
    }
}
