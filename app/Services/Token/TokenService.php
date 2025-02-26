<?php

namespace App\Services\Token;

use App\Models\Token;
use Carbon\Carbon;

class TokenService
{
    public static function insert($data)
    {
        $data['token_expired'] = Carbon::now()->addMinutes((int)env('JWT_TTL'))->toDateTimeString();
        Token::create($data);
        return true;
    }

    public static function getTokens($userId, $typeToken)
    {
        return Token::where([
            'user_id' => $userId,
            'token_type' => $typeToken
        ])->get()->pluck('token')->toArray();
    }
}
