<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\UpdateUserAdminRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function listUserForAdmin(ListUserRequest $request){
        $data = $request->all();

        $service = new UserService();
        return [
            'status' => JsonResponse::HTTP_OK,
            'body' => $service->listByAdmin($data)
        ];
    }

    public function detail($userId)
    {
        $service = new UserService($userId);

        $result = $service->detail();

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function create(CreateUserRequest $request)
    {
        $data = $request->all();

        $password = Helper::decrypt($data['password']);

        $data['password'] = Hash::make($password);
        $service = new UserService();
        $result = $service->create($data);

        return response()->json([
            'status' => JsonResponse::HTTP_CREATED,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function update(UpdateUserAdminRequest $request)
    {
        $data = $request->all();
        $service = new UserService($data['id']);
        $result = $service->update($data);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function delete($userId){
        $service = new UserService($userId);
        $result = $service->delete();

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => "Xoá người dùng thành công"
            ]
        ]);
    }
}
