# Laravel Solana SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sandermuller/laravel-solana-sdk.svg?style=flat-square)](https://packagist.org/packages/sandermuller/laravel-solana-sdk)
[![Tests](https://img.shields.io/github/actions/workflow/status/SanderMuller/laravel-solana-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/SanderMuller/laravel-solana-sdk/actions/workflows/run-tests.yml)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/SanderMuller/laravel-solana-sdk/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/SanderMuller/laravel-solana-sdk/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/sandermuller/laravel-solana-sdk.svg?style=flat-square)](https://packagist.org/packages/sandermuller/laravel-solana-sdk)
[![License](https://img.shields.io/packagist/l/sandermuller/laravel-solana-sdk.svg?style=flat-square)](LICENSE)

Laravel wrapper around [`sandermuller/solana-php-sdk`][core] — service
provider, facades, env-driven config, and artisan commands so you can
call Solana RPC from a Laravel app without wiring containers yourself.

[core]: https://github.com/SanderMuller/solana-php-sdk

```php
use SanderMuller\LaravelSolanaSdk\Facades\Solana;

$balance   = Solana::getBalance('SomeWalletAddressBase58'); // lamports as float
$blockhash = Solana::latestBlockhash();                     // typed BlockhashInfo
$status    = Solana::sendAndConfirmTransaction($tx, [$payer]);
```

## Contents

- [Requirements](#requirements)
- [Install](#install)
- [Configuration](#configuration)
- [Facades](#facades)
- [Multi-endpoint transport](#multi-endpoint-transport)
- [Confirming transactions on the queue](#confirming-transactions-on-the-queue)
- [PubSub / WebSocket](#pubsub--websocket)
- [Artisan commands](#artisan-commands)
- [Testing](#testing)
- [Upgrading](#upgrading)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Requirements

- PHP `^8.3`
- Laravel `^11.0 || ^12.0 || ^13.0`
- `ext-sodium` (transitively required by the core SDK)

## Install

```bash
composer require sandermuller/laravel-solana-sdk
```

The service provider auto-discovers. Publish the config if you want to
customise defaults:

```bash
php artisan vendor:publish --tag=solana-sdk-config
```

## Configuration

`config/solana-sdk.php` exposes four knobs:

```php
return [
    'network'          => env('SOLANA_NETWORK', 'mainnet'),
    'token_program_id' => env('SOLANA_TOKEN_PROGRAM_ID', 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA'),
    'transport' => [
        'mode'    => env('SOLANA_TRANSPORT_MODE', 'fallback'),
        'urls'    => array_values(array_filter([
            env('SOLANA_RPC_URL'),
            env('SOLANA_RPC_URL_FALLBACK'),
        ])),
        'headers' => [],
        'timeout' => (float) env('SOLANA_RPC_TIMEOUT', 30.0),
        'retry'   => [
            'max_attempts'  => (int) env('SOLANA_RPC_RETRY_ATTEMPTS', 3),
            'base_delay_ms' => (int) env('SOLANA_RPC_RETRY_BASE_MS', 100),
            'max_delay_ms'  => (int) env('SOLANA_RPC_RETRY_MAX_MS', 2_000),
        ],
    ],
    'commands' => [
        'enabled' => env('SOLANA_COMMANDS_ENABLED', true),
    ],
];
```

`SOLANA_NETWORK` accepts `mainnet`, `mainnet-beta`, `testnet`, `devnet`
(also `main`, `test`, `dev`). The RPC endpoint is derived from the
`Network` enum in the core SDK.

## Facades

```php
use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Facades\SolanaRpc;

$balance   = Solana::getBalance('SomeWalletAddressBase58');
$info      = Solana::accountInfo('SomeWalletAddressBase58'); // typed AccountInfo
$blockhash = Solana::latestBlockhash();                      // typed BlockhashInfo

// Send + poll until confirmed in a single call:
$status = Solana::sendAndConfirmTransaction($tx, [$payer]);

// Low-level JSON-RPC escape hatch
$result = SolanaRpc::call('getSlot');
```

`Solana` proxies the typed `Connection` API (~60 RPC methods covering
~80 % of the Solana JSON-RPC spec — accounts, blocks, slots, signatures,
tokens, supply, stake, vote, inflation). `SolanaRpc` proxies the raw
`SolanaRpcClient` for calls the typed facade does not yet cover.

Both bindings are also reachable via plain DI:

```php
use SanderMuller\SolanaPhpSdk\Connection;

class WalletController
{
    public function show(Connection $solana, string $address): array
    {
        return ['balance' => $solana->getBalance($address)];
    }
}
```

### Programs, builders, signers

The core SDK ships first-class program builders (`SystemProgram`,
`SplTokenProgram`, `Token2022Program`, `MemoProgram`, `StakeProgram`,
`VoteProgram`, `AddressLookupTableProgram`, `ComputeBudgetProgram`,
`MetaplexProgram`, `AnchorIdl`, …), a sanitize-safe
`TransactionBuilder`, a `Util\PriorityFee` helper, and a
`Contracts\MessageSigner` interface (with `Signing\InMemoryMessageSigner`
as the local adapter). Use them directly — no wrapping required:

```php
use SanderMuller\SolanaPhpSdk\TransactionBuilder;
use SanderMuller\SolanaPhpSdk\Programs\SystemProgram;
use SanderMuller\LaravelSolanaSdk\Facades\Solana;

$blockhash = Solana::latestBlockhash();

$tx = TransactionBuilder::make()
    ->feePayer($payer->publicKey)
    ->recentBlockhash($blockhash)
    ->addInstruction(SystemProgram::transfer($payer->publicKey, $to, $lamports))
    ->addSigner($payer)
    ->build();

$status = Solana::sendAndConfirmTransaction($tx, [$payer]);
```

## Multi-endpoint transport

Set `SOLANA_RPC_URL` (and optionally `SOLANA_RPC_URL_FALLBACK`) to route
through your own RPC provider with automatic retry + fallback. Leave
both unset to keep the public-endpoint default.

```dotenv
SOLANA_RPC_URL=https://mainnet.helius-rpc.com/?api-key=…
SOLANA_RPC_URL_FALLBACK=https://api.mainnet-beta.solana.com
SOLANA_TRANSPORT_MODE=fallback        # or round_robin
SOLANA_RPC_TIMEOUT=30
SOLANA_RPC_RETRY_ATTEMPTS=3
```

For more elaborate setups (custom auth headers, multiple fallbacks)
publish the config and edit `transport` directly — the wrapper hands
the array straight to `SanderMuller\SolanaPhpSdk\Rpc\TransportFactory`.

## Confirming transactions on the queue

This wrapper ships `SanderMuller\LaravelSolanaSdk\Queue\ConfirmTransactionJob`
out of the box. Dispatch it after `sendTransaction` so the long-tail
confirmation phase becomes a background job that fires
`TransactionConfirmed` / `TransactionExpired` events (the event classes
live in the SDK):

```php
use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Queue\ConfirmTransactionJob;

$blockhash = Solana::latestBlockhash();
$signature = Solana::sendTransaction($tx, [$payer]);

ConfirmTransactionJob::dispatch(
    signature: $signature,
    lastValidBlockHeight: $blockhash->lastValidBlockHeight,
    context: ['order_id' => $order->id],
);
```

Listen for `SanderMuller\SolanaPhpSdk\Events\TransactionConfirmed` and
`SanderMuller\SolanaPhpSdk\Events\TransactionExpired` in `EventServiceProvider`
(events are SDK-side; only the Job moved to this wrapper).

## PubSub / WebSocket

`SolanaPubSubClient` is bound transient against the configured network,
so you can typehint it directly:

```php
use SanderMuller\SolanaPhpSdk\Services\SolanaPubSubClient;

class WatchSignatures
{
    public function handle(SolanaPubSubClient $pubsub, string $signature): void
    {
        $pubsub->enableAutoReconnect();
        $pubsub->signatureSubscribe($signature);

        foreach ($pubsub->listen() as $event) {
            // …
        }
    }
}
```

## Artisan commands

| Command                            | Purpose                                     |
|------------------------------------|---------------------------------------------|
| `solana:balance {address}`         | Print SOL balance + lamports                |
| `solana:airdrop {address} {sol=1}` | Request devnet/testnet airdrop              |
| `solana:account {address}`         | Dump raw account info JSON                  |
| `solana:tx {signature}`            | Look up a transaction by signature          |
| `solana:health`                    | RPC health + version                        |
| `solana:tokens {owner}`            | List SPL token accounts owned by an address |
| `solana:fees {addresses?*}`        | Recent prioritization fee samples           |

Disable all bundled commands with `SOLANA_COMMANDS_ENABLED=false` (they
hit live RPC; production may want them off).

## Testing

```bash
composer test
```

### Stubbing RPC in tests

Call `Solana::fake()` to swap the bound `SolanaRpcClient` for the core
SDK's `InMemoryRpcStub` so the SDK never hits the network:

```php
use SanderMuller\LaravelSolanaSdk\Facades\Solana;

it('reads the balance', function (): void {
    Solana::fake()->script([
        'getBalance' => ['value' => 5_000_000],
    ]);

    expect(Solana::getBalance($address))->toBe(5_000_000.0);
    expect(Solana::fakedStub()?->methodCalls())->toContain('getBalance');
});
```

Wire the core Pest expectations (`toBeConfirmed`, `toHaveCustomCode`,
`toBeInstructionError`) once from `tests/Pest.php`:

```php
use SanderMuller\SolanaPhpSdk\Testing\PestExpectations;

PestExpectations::register();
```

The wrapper's own test suite runs against Orchestra Testbench with the
package provider auto-registered. Network-dependent tests are not
shipped — facade unit tests just verify resolution + container shape.

## Upgrading

See [UPGRADING.md](UPGRADING.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md). Updated automatically on release publish.

## Contributing

PRs welcome. Run the local gauntlet before opening one:

```bash
vendor/bin/pest          # tests
vendor/bin/pint --test   # style
vendor/bin/phpstan       # static analysis
vendor/bin/rector --dry-run
```

The package is intentionally a **thin** wrapper — net-new RPC methods,
program builders, and DTOs belong in
[`sandermuller/solana-php-sdk`][core]. The wrapper only adds Laravel
glue (`@method` lines on the `Solana` facade, env-driven config keys,
container bindings, `solana:*` commands). See
[`CLAUDE.md`](CLAUDE.md) for the full scope rules.

## Security

See [SECURITY.md](SECURITY.md).

## Credits

- [Sander Muller](https://github.com/SanderMuller)
- Wraps [`sandermuller/solana-php-sdk`](https://github.com/SanderMuller/solana-php-sdk)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
