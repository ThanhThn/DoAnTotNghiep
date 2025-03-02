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

        $tokenRequest = $ably->auth->createTokenRequest([
            "capability" => $capability,
        ]);

        $keyName = $tokenRequest->keyName;
        $timestamp = $tokenRequest->timestamp;
        $nonce = $tokenRequest->nonce;
        $mac = $tokenRequest->mac;
        $capability = $tokenRequest->capability;

        $response = Http::post("https://rest.ably.io/keys/$keyName/requestToken", [
            "keyName"    => $keyName,
            "timestamp"  => $timestamp,
            "nonce"      => $nonce,
            "mac"        => $mac,
            "capability" => $capability,
        ]);


        return $response->json();
    }
}
