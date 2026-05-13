<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Tests\Support;

use Illuminate\Contracts\Config\Repository;
use Override;
use SanderMuller\LaravelSolanaSdk\Tests\TestCase;

abstract class DisabledCommandsTestCase extends TestCase
{
    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        parent::defineEnvironment($app);

        $config = $app->make(Repository::class);
        $config->set('solana-sdk.commands.enabled', false);
    }
}
