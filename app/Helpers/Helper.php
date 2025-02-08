<?php

namespace App\Helpers;

class Helper
{
    static function encrypt($data)
    {
        $key = base64_decode(env('AES_KEY'));
        $iv = base64_decode(env('AES_IV'));
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }

    static function decrypt($data){
        $key = base64_decode(env('AES_KEY'));
        $iv = base64_decode(env('AES_IV'));
        $dataEncrypt = base64_decode($data);
        return openssl_decrypt($dataEncrypt, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
