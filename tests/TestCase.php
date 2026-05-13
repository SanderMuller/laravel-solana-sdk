<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use SanderMuller\LaravelSolanaSdk\LaravelSolanaSdkServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            LaravelSolanaSdkServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        /** @var Repository $config */
        $config = $app->make(Repository::class);

        $config->set('app.env', 'testing');
        $config->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $config->set('solana-sdk.network', 'devnet');
    }
}
