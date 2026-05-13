<?php declare(strict_types=1);

use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Services\SolanaPubSubClient;

it('binds SolanaPubSubClient against the configured network', function (): void {
    config()->set('solana-sdk.network', 'devnet');

    $pubsub = resolve(SolanaPubSubClient::class);

    expect($pubsub->network)->toBe(Network::DEVNET);
});

it('returns a fresh PubSub client per resolve (transient binding)', function (): void {
    $a = resolve(SolanaPubSubClient::class);
    $b = resolve(SolanaPubSubClient::class);

    expect($a)->not->toBe($b);
});
