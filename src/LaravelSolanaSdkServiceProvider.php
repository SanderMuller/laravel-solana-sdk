<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Override;
use SanderMuller\LaravelSolanaSdk\Console\Commands\AccountCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\AirdropCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\BalanceCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\FeesCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\HealthCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\TokensCommand;
use SanderMuller\LaravelSolanaSdk\Console\Commands\TransactionCommand;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\Enum\Network;
use SanderMuller\SolanaPhpSdk\Rpc\RpcTransport;
use SanderMuller\SolanaPhpSdk\Rpc\TransportFactory;
use SanderMuller\SolanaPhpSdk\Services\SolanaPubSubClient;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

final class LaravelSolanaSdkServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/solana-sdk.php', 'solana-sdk');

        $this->app->singleton(SolanaRpcClient::class, static function (Application $app): SolanaRpcClient {
            $config = $app->make(Repository::class);

            return new SolanaRpcClient(
                self::resolveNetwork($config->get('solana-sdk.network')),
                self::resolveTransport($config->get('solana-sdk.transport')),
            );
        });

        $this->app->bind(Connection::class, static fn (): Connection => new Connection());

        $this->app->bind(SolanaPubSubClient::class, static function (Application $app): SolanaPubSubClient {
            $config = $app->make(Repository::class);

            return new SolanaPubSubClient(self::resolveNetwork($config->get('solana-sdk.network')));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/solana-sdk.php' => $this->app->configPath('solana-sdk.php'),
        ], 'solana-sdk-config');

        if ($this->app->runningInConsole() && $this->commandsEnabled()) {
            $this->commands([
                BalanceCommand::class,
                AirdropCommand::class,
                AccountCommand::class,
                TransactionCommand::class,
                HealthCommand::class,
                TokensCommand::class,
                FeesCommand::class,
            ]);
        }
    }

    private function commandsEnabled(): bool
    {
        $config = $this->app->make(Repository::class);

        return (bool) $config->get('solana-sdk.commands.enabled', true);
    }

    private static function resolveNetwork(mixed $value): Network
    {
        if ($value instanceof Network) {
            return $value;
        }

        // Absent / unset config key keeps the mainnet default. Any other
        // unexpected type is fail-fast — silent fallback would risk
        // routing devnet-intended traffic to mainnet.
        if ($value === null) {
            return Network::MAINNET;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'solana-sdk.network must be a string alias or Network enum; got %s.',
                get_debug_type($value),
            ));
        }

        $normalized = strtolower($value);

        // Canonical names ('mainnet'/'devnet'/'testnet') round-trip
        // through the enum itself; only the aliases need an explicit match.
        return Network::tryFrom($normalized) ?? match ($normalized) {
            'mainnet-beta', 'main' => Network::MAINNET,
            'dev' => Network::DEVNET,
            'test' => Network::TESTNET,
            default => throw new InvalidArgumentException("Unknown solana network: {$value}"),
        };
    }

    /**
     * Build the optional {@see RpcTransport} stack from config. Returns
     * null only when the transport block is absent or has no `urls` — in
     * which case the SDK falls back to its default single-endpoint
     * Laravel HTTP factory. Any other shape is forwarded to
     * {@see TransportFactory::fromConfig()} so misconfiguration fails
     * loudly instead of silently routing traffic to the public endpoint.
     */
    private static function resolveTransport(mixed $value): ?RpcTransport
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'solana-sdk.transport must be an array or null; got %s.',
                get_debug_type($value),
            ));
        }

        // The default config ships `urls` as `array_filter([env(...)])`,
        // so an unset env produces an empty list. Treat that as "no
        // override" rather than as a misconfiguration.
        $urls = $value['urls'] ?? null;
        if ($urls === null || (is_array($urls) && $urls === [])) {
            return null;
        }

        /**
         * @var array{
         *     mode?: string,
         *     urls?: list<string>,
         *     headers?: array<string, string>,
         *     timeout?: float|int,
         *     retry?: array{max_attempts?: int, base_delay_ms?: int, max_delay_ms?: int}|null,
         * } $value
         */
        return TransportFactory::fromConfig($value);
    }
}
