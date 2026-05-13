<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Console\Command;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\DataObjects\Lamports;

final class AirdropCommand extends Command
{
    protected $signature = 'solana:airdrop {address : Recipient wallet address} {sol=1 : Amount of SOL to request}';

    protected $description = 'Request a devnet/testnet SOL airdrop. Fails on mainnet.';

    public function handle(Connection $connection): int
    {
        /** @var string $address */
        $address = $this->argument('address');
        /** @var string $solArg */
        $solArg = $this->argument('sol');
        $sol = (float) $solArg;

        if ($sol <= 0.0) {
            $this->error('SOL amount must be positive.');

            return self::INVALID;
        }

        $lamports = Lamports::fromSol($sol)->lamports;

        $signature = $connection->requestAirdrop([$address, $lamports]);

        $this->info(sprintf('Airdrop requested: %.4f SOL → %s', $sol, $address));
        $this->line(sprintf('Signature: %s', $signature));

        return self::SUCCESS;
    }
}
