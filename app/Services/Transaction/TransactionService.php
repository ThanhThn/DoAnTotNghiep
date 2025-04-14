<?php

namespace App\Services\Transaction;

use App\Models\Transaction;

class TransactionService
{
    function listByWallet($data, $walletId)
    {
        $transactions = Transaction::on('pgsqlReplica')->where('wallet_id', $walletId);

        if(isset($data['from'])){
            $transactions = $transactions->where('created_at', '>=', $data['from']);
        }
        if(isset($data['to'])){
            $transactions = $transactions->where('created_at', '<=', $data['to']);
        }
        if(isset($data['type'])){
            $transactions =
                $transactions->where('transaction_type', $data['type']);
        }

        $total = $transactions->count();

        $transactions = $transactions->offset($data['offset'] ?? 0)->limit($data['limit'] ?? 10)->orderBy('created_at', 'desc')->get();

        return [
            'total' => $total,
            'data' => $transactions
        ];
    }
}
