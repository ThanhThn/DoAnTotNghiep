<?php

namespace App\Services\Permission;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use App\Services\Role\RoleService;

class PermissionService
{
    private string $_lodgingId;
    public function __construct($lodgingId = null)
    {
        $this->_lodgingId = $lodgingId;
    }
    public function listAll()
    {
        return Permission::all();
    }

    public function listByUser($userId)
    {
        $roleService = new RoleService();
        $roleIds = $roleService->listUserRolesForLodging($this->_lodgingId, $userId)->pluck('id')->toArray();

        // Danh sách quyền riêng của người dùng đối với trọ
        $listNotRole = collect($this->listNotRole($userId));

        // Danh sách quyền của các vai trò trong trọ
        $listRole = collect($this->listRoles($roleIds));
        return $listNotRole->merge($listRole)->unique('id')->values();
    }

    public function listNotRole($userId)
    {
        return UserPermission::with('permission')->where([
            'user_id' => $userId,
            'lodging_id' => $this->_lodgingId,
            'is_enabled' => true
        ])->orderBy('created_at', 'desc')->get()->pluck('permission')->toArray();
    }

    public  function  listRoles($roleIds)
    {
        return RolePermission::with('permission')->whereIn('role_id', $roleIds)->where('is_enabled', true)->orderBy('created_at', 'desc')->get()->pluck('permission')->toArray();
    }
}
