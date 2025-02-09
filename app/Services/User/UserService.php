<?php

namespace App\Services\User;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private string $_userID;
    public function __construct(string $userID)
    {
        $this->_userID = $userID;
    }

    public function update($data)
    {
        $user = User::find($this->_userID);
        if(isset($data['password'])){
            $data['password'] = Hash::make(Helper::decrypt($data['password']));
        }
        $user->update($data);
        return $user->fresh();
    }
}
