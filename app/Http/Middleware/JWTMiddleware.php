<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            $authHeader = $request->header('Authorization');
            if(!$authHeader) {
                return response()->json([
                   'status' => JsonResponse::HTTP_UNAUTHORIZED,
                   'errors' => [[
                       'message' => 'Authorization header not found',
                       'field' => 'authorization'
                   ]]
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            $token = explode(' ', $authHeader);
            if(count($token) < 2) {
                return response()->json([
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                    'errors' => [[
                        'message' => 'Authorization header not found',
                        'field' => 'authorization'
                    ]]
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }
            JWTAuth::parseToken()->authenticate();
        }catch (\Exception $exception){
            if($exception instanceof  TokenInvalidException){
                return response()->json([
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                    'errors' => [[
                        'message' => 'Token is invalid',
                        'field' => 'token'
                    ]]
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            if($exception instanceof  TokenExpiredException){
                return response()->json([
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                    'errors' => [[
                        'message' => 'Token has expired',
                        'field' => 'token'
                    ]]
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => "Token not found",
                    'field' => 'token'
                ]]], JsonResponse::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
