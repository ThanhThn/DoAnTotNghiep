<?php

namespace App\Http\Controllers\Auth\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $data = $request->all();
        $password = Helper::decrypt($data["password"]);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'phone' => $data['phone'],
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json([
           'status' => JsonResponse::HTTP_OK,
            'body' => [
                'token' => $token,
            ]
        ], JsonResponse::HTTP_OK);
    }


    public function login(LoginUserRequest $request){
        $data = $request->all();
        $password = Helper::decrypt($data["password"]);

        $token = JWTAuth::attempt(['phone' => $data['phone'], 'password' => $password]);
        if(!$token){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [["message" => "Phone number or password is incorrect"]],
            ], JsonResponse::HTTP_OK);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'token' => $token,
            ]
        ]);
    }
}
