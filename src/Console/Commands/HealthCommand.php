<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\SolanaPhpSdk\Connection;

final class HealthCommand extends Command
{
    protected $signature = 'solana:health';

    protected $description = 'Check RPC node health + report version.';

    public function handle(Connection $connection): int
    {
        $health = $connection->getHealth();
        $version = $connection->getVersion();

        $this->line(sprintf('Health:  %s', $health));
        $this->line(sprintf('Version: %s', (string) json_encode($version)));

        return $health === 'ok' ? self::SUCCESS : self::FAILURE;
    }
}
