<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\LaravelSolanaSdk\Console\Commands\Concerns\RendersJsonResult;
use SanderMuller\SolanaPhpSdk\Connection;

final class AccountCommand extends Command
{
    use RendersJsonResult;

    protected $signature = 'solana:account {address : Account address (base58)}';

    protected $description = 'Display raw account info for an address.';

    public function handle(Connection $connection): int
    {
        $this->renderJson($connection->getAccountInfo($this->stringArgument('address')));

        return self::SUCCESS;
    }
}
