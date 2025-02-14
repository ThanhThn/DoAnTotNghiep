<?php

namespace App\Services\Role;

use App\Models\Role;
use App\Models\UserPermission;

class RoleService
{
//    private string $_lodgingId;
//    public function __construct($lodgingId = null)
//    {
//        $this->_lodgingId = $lodgingId;
//    }

    public function listUserRolesForLodging($lodgingId, $userId)
    {
        return UserPermission::where([
            'lodging_id' => $lodgingId,
            'user_id' => $userId
        ])->get();
    }



}
