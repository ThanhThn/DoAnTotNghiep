<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseAuthController extends Controller
{

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

    protected function addBlacklist($payload, $slug = "")
    {
        $iat = $payload->get('iat');
        $refreshExpiry = $iat + (config('jwt.refresh_ttl') * 60);
        $now = time();
        $ttl = max($refreshExpiry - $now, 0);

        JWTAuth::invalidate();
        $userId = $payload->get('sub');

        $arr = array_filter(['blacklist', $slug, $userId, $payload->get('jti')], fn($value) => (bool)$value);

        $key = implode('_', $arr);

        Redis::set($key, 1);
        Redis::expire($key, $ttl);
    }



    protected function checkBlacklist($payload, $slug = "")
    {
        $userId = $payload->get('sub');

        $arr = array_filter(['blacklist', $slug, $userId, $payload->get('jti')], fn($value) => (bool)$value);

        $key = implode('_', $arr);

        return Redis::exists($key);
    }
}
