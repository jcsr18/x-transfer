<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use App\Supports\NumericSupport;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Money\Money;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

uses(
    DatabaseMigrations::class
);

it('should transfer to user', function () {
    $senderInitialBalance = NumericSupport::floatToCents(100);
    $receiverInitialBalance = NumericSupport::floatToCents(25);

    $sender = User::factory()->create(['balance' => $senderInitialBalance]);
    $receiver = User::factory()->create(['balance' => $receiverInitialBalance]);

    $payload = [
        'from' => $sender->transfer_key,
        'to' => $receiver->transfer_key,
        'amount' => 55.90,
    ];

    $amountSentInCents = NumericSupport::floatToCents($payload['amount']);

    $response = post(route('api.transfer.store'), $payload);

    $response->assertCreated();

    assertDatabaseHas('transactions', [
        'type' => TransactionType::LOCAL_TRANSFER,
        'status' => TransactionStatus::SUCCESS,
        'amount' => $amountSentInCents,
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
    ]);

    // Sender new balance check
    assertDatabaseHas('users', [
        'transfer_key' => $sender->transfer_key,
        'balance' => Money::BRL($senderInitialBalance)->subtract(Money::BRL($amountSentInCents))->getAmount(),
    ]);

    // Receiver new balance check
    assertDatabaseHas('users', [
        'transfer_key' => $receiver->transfer_key,
        'balance' => Money::BRL($receiverInitialBalance)->add(Money::BRL($amountSentInCents))->getAmount(),
    ]);

    assertDatabaseCount('transactions', 1);
});

it('should not transfer to user when insufficient balance', function () {
    $senderInitialBalance = NumericSupport::floatToCents(100);
    $receiverInitialBalance = NumericSupport::floatToCents(25);

    $sender = User::factory()->create(['balance' => $senderInitialBalance]);
    $receiver = User::factory()->create(['balance' => $receiverInitialBalance]);
    $payload = [
        'from' => $sender->transfer_key,
        'to' => $receiver->transfer_key,
        'amount' => 101,
    ];

    $response = post(route('api.transfer.store'), $payload);

    $response->assertUnprocessable();

    // Sender balance check
    assertDatabaseHas('users', [
        'transfer_key' => $sender->transfer_key,
        'balance' => $senderInitialBalance,
    ]);

    // Receiver balance check
    assertDatabaseHas('users', [
        'transfer_key' => $receiver->transfer_key,
        'balance' => $receiverInitialBalance,
    ]);

    assertDatabaseCount('transactions', 0);
});

it('should return rate limit when fast multiples requests', function () {
    $senderInitialBalance = NumericSupport::floatToCents(100);
    $receiverInitialBalance = NumericSupport::floatToCents(25);

    $sender = User::factory()->create(['balance' => $senderInitialBalance]);
    $receiver = User::factory()->create(['balance' => $receiverInitialBalance]);

    $payload = [
        'from' => $sender->transfer_key,
        'to' => $receiver->transfer_key,
        'amount' => 25,
    ];

    $firstResponse = post(route('api.transfer.store'), $payload);
    $secondResponse = post(route('api.transfer.store'), $payload);

    $firstResponse->assertCreated();
    $secondResponse->assertTooManyRequests();

    assertDatabaseCount('transactions', 1);
    assertDatabaseHas('transactions', [
        'status' => TransactionStatus::SUCCESS,
    ]);
});
