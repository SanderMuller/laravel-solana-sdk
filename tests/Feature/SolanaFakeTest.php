<?php declare(strict_types=1);

use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

afterEach(function (): void {
    Solana::clearFake();
});

it('Solana::fake() returns an InMemoryRpcStub registered as the active fake', function (): void {
    $stub = Solana::fake();

    expect(Solana::fakedStub())->toBe($stub);
});

it('Solana::fake() routes facade calls through the stub', function (): void {
    Solana::fake()->script([
        'getBalance' => ['value' => 5_000_000],
    ]);

    expect(Solana::getBalance('FakeAddr'))->toBe(5_000_000.0)
        ->and(Solana::fakedStub()?->methodCalls())
        ->toContain('getBalance');
});

it('clearFake() drops the active stub', function (): void {
    Solana::fake();

    expect(Solana::fakedStub())->not->toBeNull();

    Solana::clearFake();

    expect(Solana::fakedStub())->toBeNull();
});

it('clearFake() restores a host-bound SolanaRpcClient that pre-existed the fake', function (): void {
    $custom = new SolanaRpcClient(Network::DEVNET);
    app()->instance(SolanaRpcClient::class, $custom);

    Solana::fake();
    expect(resolve(SolanaRpcClient::class))->not->toBe($custom);

    Solana::clearFake();

    expect(resolve(SolanaRpcClient::class))->toBe($custom);
});
