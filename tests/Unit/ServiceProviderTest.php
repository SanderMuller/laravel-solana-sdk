<?php declare(strict_types=1);

use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

it('binds SolanaRpcClient as a singleton honouring config network', function (): void {
    config()->set('solana-sdk.network', 'devnet');

    $a = resolve(SolanaRpcClient::class);
    $b = resolve(SolanaRpcClient::class);

    expect($a)->toBe($b)
        ->and($a->network)
        ->toBe(Network::DEVNET);
});

it('rebuilds the client when the container is flushed with a different network', function (): void {
    config()->set('solana-sdk.network', 'testnet');
    app()->forgetInstance(SolanaRpcClient::class);

    $client = resolve(SolanaRpcClient::class);

    expect($client->network)->toBe(Network::TESTNET);
});

it('resolves Connection as a fresh instance each time', function (): void {
    $a = resolve(Connection::class);
    $b = resolve(Connection::class);

    expect($a)->not->toBe($b);
});

it('throws for unknown network strings', function (): void {
    config()->set('solana-sdk.network', 'fakenet');
    app()->forgetInstance(SolanaRpcClient::class);

    resolve(SolanaRpcClient::class);
})->throws(InvalidArgumentException::class, 'Unknown solana network: fakenet');
