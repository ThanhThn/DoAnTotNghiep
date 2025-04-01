<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function info()
    {
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => Auth::user()
            ]
        ]);
    }

    public function update(UpdateUserRequest $request)
    {
        $data = $request->only(['full_name', 'identity_card', 'phone', 'email', 'password', 'gender', 'date_of_birth', 'address', 'relatives', 'is_active', 'is_public', 'is_completed']);

        $service = new UserService(Auth::id());
        $user = $service->update($data);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $user
            ]
        ]);
    }
}
