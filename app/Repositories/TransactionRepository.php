<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Exception;

class TransactionRepository
{
    public function draft(array $data): Transaction
    {
        $data['status'] = TransactionStatus::DRAFT;

        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->refresh();
    }

    public function success(Transaction $transaction): Transaction
    {
        return $this->update($transaction, [
            'status' => TransactionStatus::SUCCESS,
        ]);
    }

    public function error(Transaction $transaction, Exception $exception): Transaction
    {
        return $this->update($transaction, [
            'status' => TransactionStatus::FAILED,
            'error_msg' => $exception->getMessage(),
        ]);
    }
}
