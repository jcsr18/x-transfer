<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;

class UserRepository
{
    public function findByTransferKey(string $transferKey): null | User
    {
        return User::where('transfer_key', $transferKey)->first();
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function addBalance(User $user, int $amount): User
    {
        return $this->update($user, [
            'balance' => $user->balance += $amount,
        ]);
    }

    /**
     * Return last transaction when user was a sender
     *
     * @param User $user
     * @return Transaction|null
     */
    public function lastSenderTransaction(User $user): Transaction | null
    {
        return $user->ownTransactions()->latest()->first();
    }
}
