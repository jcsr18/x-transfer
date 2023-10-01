<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransferService;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LocalTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        private readonly TransactionRepository $transactionRepository = new TransactionRepository(),
    ) {}

    public function handle(): void
    {
        try {
            DB::beginTransaction();
            (new UserRepository())->addBalance($this->transaction->receiver, $this->transaction->amount);
            $this->transactionRepository->success($this->transaction);
            DB::commit();
        } catch (Exception $exception) {
            $this->fail($exception);
        }
    }

    public function fail(Exception $exception): void
    {
        $this->transactionRepository->error($this->transaction, $exception);

        (new TransferService())->restoreBalance($this->transaction->sender, $this->transaction->amount);
    }
}
