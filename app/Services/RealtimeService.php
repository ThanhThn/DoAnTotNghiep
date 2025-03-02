<?php

namespace App\Services;

use Ably\AblyRest;

class RealtimeService
{
    function getToken($userId)
    {
        $ably = new AblyRest(env('ABLY_KEY'));

        $capability = [
            "notification/user/$userId" => ["subscribe"]
        ];

        $tokenRequest = $ably->auth->createTokenRequest([
            'capability' => json_encode($capability),
        ]);

        return $tokenRequest;
    }

}
