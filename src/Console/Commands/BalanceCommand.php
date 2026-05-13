<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\DataObjects\Lamports;

final class BalanceCommand extends Command
{
    protected $signature = 'solana:balance {address : Wallet address (base58)}';

    protected $description = 'Display the SOL balance for an address.';

    public function handle(Connection $connection): int
    {
        /** @var string $address */
        $address = $this->argument('address');

        // Connection::getBalance() returns lamports as float (raw RPC `value`),
        // not SOL — round to int before wrapping in Lamports.
        $lamports = new Lamports((int) $connection->getBalance($address));

        $this->line(sprintf('Address:  %s', $address));
        $this->line(sprintf('Balance:  %.9f SOL', $lamports->toSol()));
        $this->line(sprintf('Lamports: %d', $lamports->lamports));

        return self::SUCCESS;
    }
}
