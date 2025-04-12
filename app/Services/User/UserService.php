<?php

namespace App\Services\User;

use App\Helpers\Helper;
use App\Models\Lodging;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Mockery\Exception;

class UserService
{
    private $_userId;
    public function __construct( $userId = null)
    {
        $this->_userId = $userId;
    }

    public function detail()
    {
        try{
            return User::findOrFail($this->_userId);
        }catch (\Exception $exception){
            return ['errors' => [[
                'message' => $exception->getMessage(),
            ]]];
        }
    }

    public function update($data)
    {
        $user = User::find($this->_userId);
        if(isset($data['password'])){
            $data['password'] = Hash::make(Helper::decrypt($data['password']));
        }
        $user->update($data);
        return $user->fresh();
    }

    public  function create($data)
    {
        $user = User::create($data);
        $scope = Redis::zscore('dashboard', 'current_users');
        if($scope != null){
            $scope += 1;
            Redis::zadd('dashboard', $scope, 'current_users');
        }
        return $user;
    }

    public function listByAdmin($data)
    {
        $users = User::on('pgsqlReplica');

        if(isset($data['filters'])){
            if(isset($data['filters']['name'])){
                $users = $users->where('full_name', 'ilike', '%'.$data['filters']['name'].'%');
            }
            if(isset($data['filters']['email'])){
                $users = $users->where('email', 'like', '%'.$data['filters']['email'].'%');
            }

            if(isset($data['filters']['gender'])){
                $users = $users->where('gender', $data['filters']['gender']);
            }

            if(isset($data['filters']['phone'])){
                $users = $users->where('phone', "like" ,'%'.$data['filters']['phone'].'%');
            }

            if(isset($data['filters']['identity_card'])){
                $users = $users->where('identity_card', '%'.$data['filters']['identity_card'].'%');
            }

            if(isset($data['filters']['address'])){
                $users = $users->where('address', 'like', '%'.$data['filters']['address'].'%');
            }

            if(isset($data['filters']['date_of_birth'])){
                $users = $users->where('date_of_birth', $data['filters']['date_of_birth']);
            }
        }

        $total = $users->count();
        $users = $users->offset($data['offset'] ?? 0)->limit($data['limit'] ?? 10)->get();

        return [
            'total' => $total,
            'data' => $users
        ];
    }

    public function delete()
    {
        try{
            $user = User::findOrFail($this->_userId);

            $user->delete();
            $scope = Redis::zscore('dashboard', 'current_users');
            if($scope != null){
                $scope -= 1;
                Redis::zadd('dashboard', $scope, 'current_users');
            }
            return true;
        }catch (\Exception $exception){
            return ['errors' => [[
                'message' => $exception->getMessage(),
            ]]];
        }
    }

    public function changePassword($password)
    {
        try {
            $user = User::findOrFail($this->_userId);

            $user->password = Hash::make($password);
            $user->save();

            return true;
        } catch (\Exception $exception) {
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

}
