<?php declare(strict_types=1);

use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

it('Solana facade re-resolves Connection after a runtime network flip', function (): void {
    config()->set('solana-sdk.network', 'devnet');
    app()->forgetInstance(SolanaRpcClient::class);

    $first = Solana::getFacadeRoot();
    $firstClient = $first->client;

    expect($firstClient->network)->toBe(Network::DEVNET);

    config()->set('solana-sdk.network', 'mainnet');
    app()->forgetInstance(SolanaRpcClient::class);

    $second = Solana::getFacadeRoot();
    $secondClient = $second->client;

    // Regression: default Facade caching would pin $second to the
    // devnet-bound Connection. The wrapper overrides resolveFacadeInstance
    // so each call goes through the container.
    expect($second)->not->toBe($first);
    expect($secondClient)->not->toBe($firstClient)
        ->and($secondClient->network)
        ->toBe(Network::MAINNET);
});
