<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Jobs\LocalTransferJob;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;

class TransferService
{
    private readonly TransactionRepository $transactionRepository;
    private readonly UserRepository $userRepository;

    public function __construct() {
        $this->transactionRepository = new TransactionRepository();
        $this->userRepository = new UserRepository();
    }

    public function localTransfer(User $from, User $to, int $amount): Transaction
    {
        $transaction = $this->transactionRepository->draft([
            'type' => TransactionType::LOCAL_TRANSFER,
            'sender_id' => $from->id,
            'receiver_id' => $to->id,
            'amount' => $amount,
        ]);

        $this->updateSenderBalance($from, $amount);

        dispatch(new LocalTransferJob($transaction));

        return $transaction;
    }

    private function updateSenderBalance(User $sender, int $amount): void
    {
        $newBalance = $sender->balance - $amount;

        if ($newBalance < 0) {
            throw new InsufficientBalanceException();
        }

        $this->userRepository->update($sender, [
            'balance' => $newBalance,
        ]);
    }

    public function restoreBalance(Transaction $transaction): User
    {
        return $this->userRepository->addBalance($transaction->sender, $transaction->amount);
    }
}
