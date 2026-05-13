<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\LaravelSolanaSdk\Console\Commands\Concerns\RendersJsonResult;
use SanderMuller\SolanaPhpSdk\Connection;

final class FeesCommand extends Command
{
    use RendersJsonResult;

    protected $signature = 'solana:fees {addresses?* : Optional list of addresses to scope priority fee samples}';

    protected $description = 'Show recent prioritization fee samples.';

    public function handle(Connection $connection): int
    {
        /** @var array<int, string> $addresses */
        $addresses = (array) $this->argument('addresses');

        $this->renderJson($connection->getRecentPrioritizationFees($addresses === [] ? null : $addresses));

        return self::SUCCESS;
    }
}
