<?php

namespace App\Services\Token;

use App\Models\Token;
use Carbon\Carbon;

class TokenService
{
    public static function insert($data)
    {
        $dataOrigin = $data;
        $data['token_expired'] = Carbon::now()->addMinutes((int)env('JWT_TTL'))->toDateTimeString();
        Token::updateOrCreate($dataOrigin, $data);
        return true;
    }

    public static function getTokens($userId, $typeToken, $rule)
    {
        return Token::where([
            'user_id' => $userId,
            'token_type' => $typeToken,
            'rule' => $rule
        ])->get()->pluck('token')->toArray();
    }

    static function removeToken($userId, $typeToken, $token)
    {
        Token::where([
            'user_id' => $userId,
            'token_type' => $typeToken,
            'token' => $token
        ])->delete();
    }
}
