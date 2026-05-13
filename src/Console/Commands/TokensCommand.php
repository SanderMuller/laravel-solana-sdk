<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use SanderMuller\LaravelSolanaSdk\Console\Commands\Concerns\RendersJsonResult;
use SanderMuller\SolanaPhpSdk\Connection;

final class TokensCommand extends Command
{
    use RendersJsonResult;

    protected $signature = 'solana:tokens {owner : Wallet address that owns the token accounts}';

    protected $description = 'List SPL token accounts owned by an address.';

    public function handle(Connection $connection, Repository $config): int
    {
        /** @var string $programId */
        $programId = $config->get('solana-sdk.token_program_id');

        $this->renderJson($connection->getTokenAccountsByOwner(
            $this->stringArgument('owner'),
            ['programId' => $programId],
        ));

        return self::SUCCESS;
    }
}
