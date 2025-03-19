<?php

namespace App\Http\Controllers\Auth\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\Token\TokenService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
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

    public function logout()
    {
        $payload = JWTAuth::parseToken()->getPayload();

        $this->addBlacklist($payload);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body'  => [
                'data' => [
                    'message' => 'Successfully logged out'
                ]
            ]
        ]);
    }

    public function refresh()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $newToken = JWTAuth::parseToken()->refresh();

            $this->addBlacklist($payload);
            return $this->respondWithToken($newToken);
        } catch (\Exception $exception) {
            if($exception instanceof TokenExpiredException){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [['message' => 'Refresh token expired']],
            ]);}

            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [['message' => 'Something went wrong']],
            ]);
        }
    }

    protected function addBlacklist($payload)
    {
        $exp = $payload->get('exp');
        $now = time();
        $ttl = max($exp - $now, 0);

        JWTAuth::invalidate();
        $userId = $payload->get('sub');

        $key = "blacklist_{$userId}_{$payload->get('jti')}";

        Redis::set($key, 1);
        Redis::expire($key, $ttl);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }
}
