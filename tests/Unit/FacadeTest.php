<?php declare(strict_types=1);

use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Facades\SolanaRpc;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

it('Solana facade resolves to a Connection instance', function (): void {
    expect(Solana::getFacadeRoot())->toBeInstanceOf(Connection::class);
});

it('SolanaRpc facade resolves to a SolanaRpcClient singleton', function (): void {
    expect(SolanaRpc::getFacadeRoot())->toBeInstanceOf(SolanaRpcClient::class);
});
