<?php

namespace App\Services;

use Ably\AblyRest;
use Illuminate\Support\Facades\Http;

class RealtimeService
{
    public function getToken($userId)
    {
        $ably = new AblyRest(env('ABLY_KEY'));

        $capability = [
            "notification/user/$userId" => ["subscribe"]
        ];

        $keyParts = explode(":", env('ABLY_KEY'));
        $ablyKeyName = $keyParts[0];

        $tokenRequest = $ably->auth->createTokenRequest([
            "capability" => json_encode($capability),
        ]);

        $response = Http::post("https://rest.ably.io/keys/$ablyKeyName/requestToken", [
            "keyName"    => $tokenRequest['keyName'],
            "timestamp"  => $tokenRequest['timestamp'],
            "nonce"      => $tokenRequest['nonce'],
            "mac"        => $tokenRequest['mac'],
            "capability" => $tokenRequest['capability'],
        ]);
        return $response->json();
    }

}
