<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Helpers\Helper;

class AuthService
{
    public static function renderOTP($key){
        $otp = rand(1000, 9999);

        $hashedOtp = Hash::make($otp);
        Redis::setex("otp:{$key}", 5*60, $hashedOtp);
        return $otp;
    }

    public static function verifyOTP($key,$otp){
        $cachedOtp = Redis::get("otp:{$key}");

        if(!$cachedOtp){
            return [
                'errors' => [
                    'message' => "OTP expired or not found."
                ]
            ];
        }

        if(!Hash::check($otp,$cachedOtp)){
            return ['errors' => [[
                'message' => "Invalid OTP."
            ]]];
        }

        $resetToken = Str::random(64);
        $tokenEncrypted = Helper::encrypt($resetToken);
        Redis::setex("reset_token:{$key}", 5*60, $tokenEncrypted);

        return $resetToken;
    }

    public static function resetPassword($key, $password, $token, $user){
        $storedToken = Redis::get("reset_token:{$key}");
        $tokenEncrypted = Helper::encrypt($token);

        if(!$storedToken || $tokenEncrypted != $storedToken){
            return ["errors" => [["message" => "Invalid or expired token"]]];
        }

        $user->update([
            'password' => Hash::make(Helper::decrypt($password)),
        ]);

        Redis::del("reset_token:{$key}");
        Redis::del("otp:{$key}");
        return "Password reset successfully.";
    }
}
