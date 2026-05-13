<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\LaravelSolanaSdk\Console\Commands\Concerns\RendersJsonResult;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\DataObjects\TransactionStatement;

final class TransactionCommand extends Command
{
    use RendersJsonResult;

    protected $signature = 'solana:tx {signature : Transaction signature (base58)}';

    protected $description = 'Display a transaction by signature.';

    public function handle(Connection $connection): int
    {
        $tx = $connection->getTransaction($this->stringArgument('signature'));

        if (! $tx instanceof TransactionStatement) {
            $this->warn('Transaction not found.');

            return self::FAILURE;
        }

        $this->renderJson($tx);

        return self::SUCCESS;
    }
}
