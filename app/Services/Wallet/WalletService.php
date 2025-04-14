<?php

namespace App\Services\Wallet;

use App\Models\Lodging;
use App\Models\User;
use App\Models\Wallet;
use Mockery\Exception;

class WalletService
{
    public function detail($walletId)
    {
        try {
            return Wallet::findOrFail($walletId);
        }catch (Exception $exception){
            return ["errors" => [[
                "message" => $exception->getMessage(),
            ]]];
        }
    }

    static function isOwnerWallet($walletId, $userId)
    {
        $wallet = Wallet::on('pgsqlReplica')->with('walletable')->find($walletId);

        if (!$wallet) {
            return false;
        }

        $owner = $wallet->walletable;

        if ($owner instanceof User) {
            return $owner->id == $userId;
        }

        if ($owner instanceof Lodging) {
            return $owner->user_id == $userId;
        }

        return false;
    }
}
