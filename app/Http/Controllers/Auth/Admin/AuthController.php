<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAdminRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\BaseRequest;
use App\Mail\OTPEmail;
use App\Models\AdminUser;
use App\Models\User;
use App\Services\AuthService;
use App\Services\Token\TokenService;
use App\Services\User\UserService;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\throwException;

class AuthController extends BaseAuthController
{
    public function login(LoginAdminRequest $request){
        $data = $request->all();
        $password = Helper::decrypt($data["password"]);

        $token = Auth::guard('admin')->attempt(['email' => $data['email'], 'password' => $password]);
        if(!$token){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [["message" => "Email or password is incorrect"]],
            ], JsonResponse::HTTP_OK);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        $token = JWTAuth::parseToken();

        $token->invalidate(true);

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

    public function requestOTP(BaseRequest $request)
    {
        $request->validate(['email' => 'required|email|exists:admin_users,email']);

        try {
            $otp  = AuthService::renderOTP($request->email);
            $user = AdminUser::on('pgsqlReplica')->where('email', $request->email)->first();

            Mail::to($request->email)->send(new OTPEmail($user->username, $otp, "Yêu cầu đổi mật khẩu"));

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'body' => [
                    'data' => 'OTP sent to your email'
                ]
            ]);
        }catch (Exception $exception){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [['message' => $exception->getMessage()]],
            ]);
        }

    }

    public function verifyOTP(BaseRequest $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        $result = AuthService::verifyOTP($request->email, $request->otp);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body'   => [
                'data' => $result
            ]
        ]);
    }

    public function resetPassword(BaseRequest $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admin_users,email',
            'token' => 'required|string',
            'password' => 'required|string',
        ]);
        $data = $request->all();
        $user = AdminUser::where("email", $data['email'])->first();
        $result = AuthService::resetPassword($data['email'], $data['password'], $data["token"],$user);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
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
}
