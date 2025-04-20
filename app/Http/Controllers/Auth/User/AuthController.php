<?php

namespace App\Http\Controllers\Auth\User;

use App\Helpers\Helper;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\BaseRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\Lodging\LodgingService;
use App\Services\Notification\NotificationService;
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
        if(isset($data['token'])){
            TokenService::insert([
                'token' => $data['token'],
                'user_id' => $user->id,
                'device' => $device,
                'token_type' => config('constant.token.type.notify'),
                'rule' => "user"
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

        $user = Auth::user();
        if($data['rule'] == config('constant.rule.manager')){
            $lodgingService = new LodgingService();
            $result = $lodgingService->listByUserID($user->id);
            if($result->count() <= 0){
                return response()->json([
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                    'errors' => [
                        ["message" => "Không có nhà cho thuê để quản lý.",
                        "field" => "rule"]],
                ]);
            }
        }

        $tokenHasRule = JWTAuth::claims([
            'rule' => $data['rule']
        ])->fromUser($user);

        if(isset($data['token'])){
            TokenService::insert([
                'token' => $data['token'],
                'user_id' => Auth::id(),
                'device' => $device,
                'token_type' => config('constant.token.type.notify'),
                'rule' => $data['rule']
            ]);
        }

        return $this->respondWithToken($tokenHasRule);
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

    public function requestOTP(BaseRequest $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/',
                'exists:users,phone',
            ],
            "token" => "nullable|string"
        ]);

        try {
            $otp = AuthService::renderOTP($request->phone);
            Log::info("OTP: $otp");

            if(isset($request->token)){
                NotificationService::sendNotificationRN([
                    'title' => 'Xác Thực OTP',
                    'body' => "Mã xác thực (OTP) của bạn là: $otp. Mã có hiệu lực trong vòng 5 phút.",
                    'target_url' => "/auth/verify_otp"
                ], [$request->token]);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'body' => [
                    'data' => 'OTP sent to your email'
                ]
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [['message' => $exception->getMessage()]],
            ]);
        }

    }

    public function verifyOTP(BaseRequest $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/',
                'exists:users,phone',
            ],
            'otp' => 'required|string',
        ]);

        $result = AuthService::verifyOTP($request->phone, $request->otp);

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
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/',
                'exists:users,phone',
            ],
            'token' => 'required|string',
            'password' => 'required|string',
        ]);
        $data = $request->all();
        $user = User::where("phone", $data['phone'])->first();
        $result = AuthService::resetPassword($data['phone'], $data['password'], $data["token"], $user);

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
