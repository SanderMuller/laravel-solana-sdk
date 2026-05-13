<?php declare(strict_types=1);

use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

dataset('network_aliases', [
    ['mainnet',      Network::MAINNET],
    ['mainnet-beta', Network::MAINNET],
    ['main',         Network::MAINNET],
    ['MAINNET',      Network::MAINNET],
    ['devnet',       Network::DEVNET],
    ['dev',          Network::DEVNET],
    ['testnet',      Network::TESTNET],
    ['test',         Network::TESTNET],
]);

it('maps string aliases to the right Network enum', function (string $alias, Network $expected): void {
    config()->set('solana-sdk.network', $alias);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(resolve(SolanaRpcClient::class)->network)->toBe($expected);
})->with('network_aliases');

it('falls back to MAINNET when config returns null (key absent)', function (): void {
    config()->set('solana-sdk.network');
    app()->forgetInstance(SolanaRpcClient::class);

    expect(resolve(SolanaRpcClient::class)->network)->toBe(Network::MAINNET);
});

it('accepts a Network enum instance directly without coercion', function (): void {
    config()->set('solana-sdk.network', Network::TESTNET);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(resolve(SolanaRpcClient::class)->network)->toBe(Network::TESTNET);
});

it('fails loudly when network config is an unexpected type', function (): void {
    config()->set('solana-sdk.network', 123);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(fn () => resolve(SolanaRpcClient::class))
        ->toThrow(InvalidArgumentException::class);
});
