<?php

namespace App\Helpers;

use Carbon\Carbon;

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

    static function generateUniqueCode($val1, $val2) {
        $merged = strtoupper(substr(hash('sha256', $val1 . $val2), 0, 6));

        return $merged;
    }

    static function calculateDuration($fromDate, $toDate, $isCutoffTime = false) {
        $fromMoment = Carbon::parse($fromDate);
        $toMoment = Carbon::parse($toDate);

        if ($fromMoment->isSameDay($toMoment)) {
            return !$isCutoffTime
                ? ['months' => 1, 'days' => 0]
                : ['months' => 0, 'days' => 1];
        }

        $months = ($toMoment->year - $fromMoment->year) * 12 + ($toMoment->month - $fromMoment->month);

        // Tính số ngày
        $days = $toMoment->day - $fromMoment->day;
        if ($days < 0) {
            $lastMonthDays = $fromMoment->copy()->subMonth()->daysInMonth;
            $days += $lastMonthDays;
            return ['months' => $months - 1, 'days' => $days]; // Giảm 1 tháng nếu phải cộng ngày
        }

        return ['months' => $months, 'days' => $days];
    }

    static function formatVietnamNumber($number)
    {

        $number = preg_replace('/\D/', '', $number);


        if (strpos($number, '0') === 0) {
            return '+84' . substr($number, 1);
        }

        if (strpos($number, '+84') === 0) {
            return $number;
        }

        if (strpos($number, '84') === 0) {
            return '+' . $number;
        }

        return $number;
    }

}
