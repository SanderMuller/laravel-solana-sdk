<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Override;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;

/**
 * Facade proxy for the low-level JSON-RPC client. Refer to
 * {@see SolanaRpcClient} for the public API.
 *
 * @method static array{jsonrpc: string, id: string, method: string, params: array<mixed>} buildRpc(string $method, array<int|string, mixed> $params = [])
 * @method static mixed                                                                      call(string $method, array<int|string, mixed> $params = [])
 *
 * @see SolanaRpcClient
 */
final class SolanaRpc extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return SolanaRpcClient::class;
    }
}
