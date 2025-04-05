<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return $this->unauthorized('Authorization header not found');
        }

        try {

            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub');

            $cacheKey = 'user_admin:'.$userId;
            $cachedUser = Redis::get($cacheKey);
            if ($cachedUser) {
                $user = unserialize($cachedUser);
                Auth::setUser($user);
            }else{
                $user = AdminUser::on('pgsqlReplica')->findOrFail($userId);

                Auth::setUser($user);

                Redis::setex($cacheKey, 3600, serialize($user));
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        return trim(str_replace('Bearer', '', $authHeader));
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'status' => JsonResponse::HTTP_UNAUTHORIZED,
            'errors' => [['message' => $message, 'field' => 'token']]
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    private function handleException(\Exception $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof TokenInvalidException => $this->unauthorized('Token is invalid'),
            $exception instanceof TokenExpiredException => $this->unauthorized('Token has expired'),
            default => $this->unauthorized('Unauthorized'),
        };
    }
}
