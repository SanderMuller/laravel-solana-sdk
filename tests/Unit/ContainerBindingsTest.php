<?php declare(strict_types=1);

use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Facades\SolanaRpc;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

it('Solana facade and Connection container key share no instance (transient binding)', function (): void {
    $a = resolve(Connection::class);
    $b = Solana::getFacadeRoot();

    expect($b)->toBeInstanceOf(Connection::class)
        ->and($a)->not->toBe($b);
});

it('SolanaRpc facade and direct container resolution return the same singleton', function (): void {
    $a = resolve(SolanaRpcClient::class);
    $b = SolanaRpc::getFacadeRoot();

    expect($a)->toBe($b);
});

it('Connection trait pulls the wrapper-configured SolanaRpcClient from the global container', function (): void {
    config()->set('solana-sdk.network', 'devnet');
    app()->forgetInstance(SolanaRpcClient::class);

    $client = resolve(SolanaRpcClient::class);
    $connection = new Connection();

    // The trait uses Container::getInstance(), and Orchestra Testbench
    // assigns the Laravel app to be the global container instance.
    expect($connection->client)->toBe($client);
});
