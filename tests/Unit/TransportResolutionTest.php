<?php declare(strict_types=1);

use SanderMuller\SolanaPhpSdk\Rpc\FallbackTransport;
use SanderMuller\SolanaPhpSdk\Rpc\RoundRobinTransport;
use SanderMuller\SolanaPhpSdk\Rpc\RpcTransport;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

/**
 * Reads the private `$transport` property off the client so the test
 * does not need a live RPC endpoint. Brittle if the core SDK renames
 * the field — accept that trade-off rather than running real HTTP.
 */
function transportOf(SolanaRpcClient $client): ?RpcTransport
{
    /** @var ?RpcTransport $transport */
    $transport = (fn () => $this->transport)->call($client);

    return $transport;
}

it('binds a single-endpoint transport when one URL is configured', function (): void {
    config()->set('solana-sdk.transport', [
        'mode' => 'fallback',
        'urls' => ['https://api.example.com'],
        'headers' => [],
        'timeout' => 30.0,
        'retry' => null,
    ]);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(transportOf(resolve(SolanaRpcClient::class)))->toBeInstanceOf(RpcTransport::class);
});

it('wraps multiple URLs in a FallbackTransport by default', function (): void {
    config()->set('solana-sdk.transport', [
        'urls' => ['https://a.example.com', 'https://b.example.com'],
    ]);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(transportOf(resolve(SolanaRpcClient::class)))->toBeInstanceOf(FallbackTransport::class);
});

it('honours mode=round_robin when configured', function (): void {
    config()->set('solana-sdk.transport', [
        'mode' => 'round_robin',
        'urls' => ['https://a.example.com', 'https://b.example.com'],
    ]);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(transportOf(resolve(SolanaRpcClient::class)))->toBeInstanceOf(RoundRobinTransport::class);
});

it('leaves transport null when urls is empty', function (): void {
    config()->set('solana-sdk.transport', [
        'urls' => [],
    ]);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(transportOf(resolve(SolanaRpcClient::class)))->toBeNull();
});

it('leaves transport null when the whole array is null', function (): void {
    config()->set('solana-sdk.transport');
    app()->forgetInstance(SolanaRpcClient::class);

    expect(transportOf(resolve(SolanaRpcClient::class)))->toBeNull();
});

it('fails loudly when transport config is not an array or null', function (): void {
    config()->set('solana-sdk.transport', 'https://api.example.com');
    app()->forgetInstance(SolanaRpcClient::class);

    expect(fn () => resolve(SolanaRpcClient::class))
        ->toThrow(InvalidArgumentException::class);
});

it('fails loudly when an unknown transport mode is configured', function (): void {
    config()->set('solana-sdk.transport', [
        'mode' => 'broadcast', // not a valid mode
        'urls' => ['https://a.example.com', 'https://b.example.com'],
    ]);
    app()->forgetInstance(SolanaRpcClient::class);

    expect(fn () => resolve(SolanaRpcClient::class))
        ->toThrow(InvalidArgumentException::class);
});
