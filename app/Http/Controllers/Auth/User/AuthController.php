<?php

namespace App\Http\Controllers\Auth\User;

use App\Helpers\Helper;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\Token\TokenService;
use App\Services\User\UserService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\throwException;

class AuthController extends BaseAuthController
{
    public function register(RegisterUserRequest $request)
    {
        $data = $request->all();
        $password = Helper::decrypt($data["password"]);
        $device = request()->header('User-Agent');

        $user = (new UserService())->create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'phone' => $data['phone'],
        ]);

        $token = JWTAuth::fromUser($user);
        TokenService::insert([
            'token' => $token,
            'user_id' => $user->id,
            'device' => $device,
            'token_type' => config('constant.token.type.login')
        ]);
        if($data['token']){
            TokenService::insert([
                'token' => $data['token'],
                'user_id' => Auth::id(),
                'device' => $device,
                'token_type' => config('constant.token.type.notify')
            ]);
        }
        return $this->respondWithToken($token);
    }


    public function login(LoginUserRequest $request){
        $data = $request->all();
        $password = Helper::decrypt($data["password"]);

        $device = request()->header('User-Agent');

        $token = JWTAuth::attempt(['phone' => $data['phone'], 'password' => $password]);


        if(!$token){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [["message" => "Phone number or password is incorrect"]],
            ], JsonResponse::HTTP_OK);
        }

        if(isset($data['token'])){
            TokenService::insert([
                'token' => $data['token'],
                'user_id' => Auth::id(),
                'device' => $device,
                'token_type' => config('constant.token.type.notify')
            ]);
        }

        return $this->respondWithToken($token);
    }

    public function logout(Request $request)
    {
        $data = $request->all();
        $token = JWTAuth::parseToken();

        $token->invalidate(true);

        if(isset($data['token'])){
            TokenService::removeToken(Auth::id(), config('constant.token.type.notify'), $data['token']);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body'  => [
                'data' => 'Successfully logged out'
            ]
        ]);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::parseToken();

            $newToken = Auth::guard('admin')->refresh();

            $token->invalidate(true);
            return $this->respondWithToken($newToken);
        } catch (\Exception $exception) {
            if($exception instanceof TokenExpiredException){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [['message' => 'Refresh token expired']],
            ], JsonResponse::HTTP_UNAUTHORIZED);}

            if($exception instanceof TokenInvalidException){
                return response()->json([
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                    'errors' => [['message' => 'Refresh token invalid']],
                ]);
            }
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [['message' => 'Something went wrong']],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }
}
