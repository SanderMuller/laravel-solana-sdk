<?php declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Queue\ConfirmTransactionJob;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\Events\TransactionConfirmed;
use SanderMuller\SolanaPhpSdk\Events\TransactionExpired;
use SanderMuller\SolanaPhpSdk\Events\TransactionExpiredReason;

it('fires TransactionConfirmed when the signature status lands at the requested commitment', function (): void {
    Event::fake();

    Solana::fake()->script([
        'getSignatureStatuses' => [
            'context' => ['slot' => 1],
            'value' => [[
                'slot' => 100,
                'confirmations' => null,
                'confirmationStatus' => 'confirmed',
                'err' => null,
            ]],
        ],
    ]);

    $job = new ConfirmTransactionJob(
        signature: 'sig-1',
        pollIntervalMs: 1,
        context: ['order_id' => 99],
    );

    $job->handle(resolve(Connection::class));

    Event::assertDispatched(
        TransactionConfirmed::class,
        static fn (TransactionConfirmed $e): bool => $e->signature === 'sig-1'
            && $e->context['order_id'] === 99,
    );
});

it('fires TransactionExpired with FailedOnChain reason when getSignatureStatuses reports an err payload', function (): void {
    Event::fake();

    Solana::fake()->script([
        'getSignatureStatuses' => [
            'context' => ['slot' => 1],
            'value' => [[
                'slot' => 100,
                'confirmations' => null,
                'confirmationStatus' => 'confirmed',
                'err' => ['InstructionError' => [0, ['Custom' => 1]]],
            ]],
        ],
    ]);

    $job = new ConfirmTransactionJob(signature: 'sig-fail', pollIntervalMs: 1);
    $job->handle(resolve(Connection::class));

    Event::assertDispatched(
        TransactionExpired::class,
        static fn (TransactionExpired $e): bool => $e->reason === TransactionExpiredReason::FailedOnChain,
    );
});
