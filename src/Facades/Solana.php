<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Facades;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;
use Override;
use SanderMuller\SolanaPhpSdk\Connection;
use SanderMuller\SolanaPhpSdk\DataObjects\AccountInfo;
use SanderMuller\SolanaPhpSdk\DataObjects\BlockhashInfo;
use SanderMuller\SolanaPhpSdk\DataObjects\ProgramAccount;
use SanderMuller\SolanaPhpSdk\DataObjects\SignatureStatus;
use SanderMuller\SolanaPhpSdk\DataObjects\TransactionStatement;
use SanderMuller\SolanaPhpSdk\Enum\Encoding;
use SanderMuller\SolanaPhpSdk\PublicKey;
use SanderMuller\SolanaPhpSdk\Services\SolanaRpcClient;
use SanderMuller\SolanaPhpSdk\Testing\InMemoryRpcStub;
use SanderMuller\SolanaPhpSdk\Transaction;
use SanderMuller\SolanaPhpSdk\Util\Commitment;
use SanderMuller\SolanaPhpSdk\VersionedTransaction;

/**
 * Facade proxy for the SDK's typed Solana RPC client. Refer to
 * {@see Connection} for the full method catalogue (60+ public methods).
 *
 * The underlying `Connection` container binding is transient and its
 * `IsProgram` trait memoises a `SolanaRpcClient` per instance. Laravel's
 * default `Facade` caches the first-resolved instance, which would pin
 * a `Solana::*` caller to a stale `SolanaRpcClient` after a runtime
 * network flip (`config()->set('solana-sdk.network', ...)` +
 * `app()->forgetInstance(SolanaRpcClient::class)`). This facade
 * therefore re-resolves on every call.
 *
 * Account / balance:
 * @method static AccountInfo            accountInfo(PublicKey|string $walletAddress, Encoding|string $encoding = Encoding::BASE64)
 * @method static array<string, mixed>   getAccountInfo(PublicKey|string $walletAddress)
 * @method static array<int, AccountInfo|null> multipleAccounts(array<int, PublicKey|string> $publicKeys, Encoding|string $encoding = Encoding::BASE64)
 * @method static float                  getBalance(string $walletAddress)
 * @method static int                    getMinimumBalanceForRentExemption(int $space = 1024)
 * @method static array<int, mixed>      getLargestAccounts(array<string, mixed> $options = [])
 *
 * Block / slot / cluster info:
 * @method static int                    getSlot(?Commitment $commitment = null)
 * @method static int                    getBlockHeight(?Commitment $commitment = null)
 * @method static int                    getTransactionCount(?Commitment $commitment = null)
 * @method static string                 getHealth()
 * @method static array<string, mixed>   getVersion()
 * @method static array<string, mixed>   getIdentity()
 * @method static array<int, mixed>      getClusterNodes()
 * @method static array<string, mixed>   getEpochInfo(?Commitment $commitment = null)
 * @method static array<string, mixed>   getEpochSchedule()
 * @method static array<string, mixed>   getHighestSnapshotSlot()
 * @method static string                 getGenesisHash()
 * @method static int                    getFirstAvailableBlock()
 * @method static int                    minimumLedgerSlot()
 * @method static int                    getMaxRetransmitSlot()
 * @method static int                    getMaxShredInsertSlot()
 * @method static string                 getSlotLeader(?Commitment $commitment = null)
 * @method static array<int, string>     getSlotLeaders(int $startSlot, int $limit)
 *
 * Blocks / blockhash / fees:
 * @method static BlockhashInfo          latestBlockhash(?Commitment $commitment = null)
 * @method static array<string, mixed>   getLatestBlockhash(?Commitment $commitment = null)
 * @method static array<string, mixed>|null getBlock(int $slot, array<string, mixed> $options = [])
 * @method static array<int, int>        getBlocks(int $startSlot, ?int $endSlot = null, ?Commitment $commitment = null)
 * @method static array<int, int>        getBlocksWithLimit(int $startSlot, int $limit, ?Commitment $commitment = null)
 * @method static int|null               getBlockTime(int $slot)
 * @method static array<string, mixed>   getBlockCommitment(int $slot)
 * @method static array<string, mixed>   getBlockProduction(array<string, mixed> $options = [])
 * @method static array<int, mixed>|null getLeaderSchedule(?int $slot = null, array<string, mixed> $options = [])
 * @method static bool                   isBlockhashValid(string $blockhash, ?Commitment $commitment = null)
 * @method static int|null               getFeeForMessage(string $base64Message, ?Commitment $commitment = null)
 * @method static array<int, mixed>      getRecentPrioritizationFees(?array<int, string> $addresses = null)
 * @method static array<int, mixed>      getRecentPerformanceSamples(?int $limit = null)
 *
 * Programs / GPA:
 * @method static array<int, ProgramAccount> programAccounts(PublicKey|string $programId, array<string, mixed> $options = [])
 * @method static mixed                  getProgramAccounts(string $programIdBs58, ?array<string, mixed> $dataSlice = null, ?array<int, array<string, mixed>> $filters = null, Encoding|string $encoding = Encoding::BASE64, array<string, mixed> $extraConfig = [])
 * @method static array<int, mixed>      programAccountsPaged(PublicKey|string $programId, array<string, mixed> $options = [])
 *
 * Signatures / transactions:
 * @method static array<int, mixed>      getSignaturesForAddress(PublicKey|string $address, array<string, mixed> $options = [])
 * @method static array<int, mixed>      getSignatureStatuses(array<int, string> $signatures, bool $searchTransactionHistory = false)
 * @method static TransactionStatement|null getTransaction(string $transactionSignature, ?Commitment $commitment = null)
 * @method static mixed                  sendTransaction(Transaction $transaction, array<int, mixed> $signers, array<string, mixed> $params = [])
 * @method static array<string, mixed>   simulateTransaction(Transaction $transaction, array<int, mixed> $signers, array<string, mixed> $params = [])
 * @method static string                 sendRawTransaction(string $rawTransaction, array<string, mixed> $params = [])
 * @method static string                 sendVersionedTransaction(VersionedTransaction $transaction, array<string, mixed> $params = [])
 * @method static SignatureStatus        sendAndConfirmTransaction(Transaction $transaction, array<int, mixed> $signers, array<string, mixed> $params = [], ?Commitment $commitment = null, int $timeoutSeconds = 60, int $pollIntervalMs = 500)
 * @method static SignatureStatus        sendAndConfirmVersionedTransaction(VersionedTransaction $transaction, array<string, mixed> $params = [], ?Commitment $commitment = null, int $timeoutSeconds = 60, int $pollIntervalMs = 500)
 * @method static SignatureStatus        confirmTransaction(string $signature, ?Commitment $commitment = null, ?int $lastValidBlockHeight = null, int $timeoutSeconds = 60, int $pollIntervalMs = 500)
 * @method static string                 requestAirdrop(array<int|string, mixed> $params = [])
 *
 * Tokens:
 * @method static array<string, mixed>   getTokenAccountBalance(PublicKey|string $tokenAccount, ?Commitment $commitment = null)
 * @method static array<string, mixed>   getTokenSupply(PublicKey|string $mint, ?Commitment $commitment = null)
 * @method static array<int, mixed>      getTokenAccountsByOwner(PublicKey|string $owner, array<string, mixed> $filter, Encoding|string $encoding = Encoding::JSON_PARSED)
 * @method static array<int, mixed>      getTokenAccountsByDelegate(PublicKey|string $delegate, array<string, mixed> $filter, Encoding|string $encoding = Encoding::JSON_PARSED)
 * @method static array<int, mixed>      getTokenLargestAccounts(PublicKey|string $mint, ?Commitment $commitment = null)
 *
 * Supply / stake / vote / inflation:
 * @method static array<string, mixed>   getSupply(?Commitment $commitment = null)
 * @method static array<string, mixed>   getVoteAccounts(?Commitment $commitment = null)
 * @method static array<string, mixed>   getInflationGovernor(?Commitment $commitment = null)
 * @method static array<string, mixed>   getInflationRate()
 * @method static array<int, mixed>      getInflationReward(array<int, string> $addresses, ?int $epoch = null, ?Commitment $commitment = null)
 * @method static array<string, mixed>   getStakeActivation(PublicKey|string $stakeAccount, array<string, mixed> $options = [])
 * @method static int                    getStakeMinimumDelegation(?Commitment $commitment = null)
 *
 * @see Connection
 *
 * @api
 */
final class Solana extends Facade
{
    /**
     * The active {@see InMemoryRpcStub}, set by {@see fake()}. Null when
     * the facade is operating against the real (or container-bound)
     * client. Mirrors the helper on the core SDK's facade so Laravel
     * tests can stub RPC without reaching into the core namespace.
     */
    private static ?InMemoryRpcStub $activeFake = null;

    /**
     * Snapshot of a `SolanaRpcClient` previously bound as a shared
     * container instance — captured the first time {@see fake()} swaps
     * it out, restored on {@see clearFake()} so a user's custom binding
     * (e.g. an integration-test client wired before the fake) survives
     * the round-trip. Null means "no prior shared binding to restore"
     * (the service provider's singleton closure rebuilds on its own).
     */
    private static ?SolanaRpcClient $priorBoundClient = null;

    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Connection::class;
    }

    #[Override]
    protected static function resolveFacadeInstance(mixed $name): mixed
    {
        if (is_object($name)) {
            return $name;
        }

        return self::getFacadeApplication()?->make($name);
    }

    /**
     * Swap the bound {@see SolanaRpcClient} for an {@see InMemoryRpcStub}
     * and return the stub so the test can script responses + assert call
     * sequences:
     *
     * ```php
     * Solana::fake()->script([
     *     'getBalance' => ['value' => 5_000_000],
     * ]);
     *
     * // getBalance returns the raw `value` field — lamports, not SOL.
     * expect(Solana::getBalance($address))->toBe(5_000_000.0);
     * expect(Solana::fakedStub()?->methodCalls())->toContain('getBalance');
     * ```
     */
    public static function fake(): InMemoryRpcStub
    {
        $stub = new InMemoryRpcStub();

        $app = self::resolveContainer();
        if ($app instanceof IlluminateContainer) {
            // Capture any already-resolved shared instance so clearFake()
            // can restore it. The service provider's singleton closure is
            // left alone — it rebuilds on its own after forgetInstance().
            // Restoration only matters for hosts that did `app->instance(...)`
            // upfront.
            self::$priorBoundClient = self::capturePriorClient($app);

            $app->instance(SolanaRpcClient::class, $stub->client());
        }

        // Connection is bound transient — no shared instance to forget.
        // clearResolvedInstance still drops anything `Solana::swap()` may
        // have parked in the facade-level cache.
        self::clearResolvedInstance(Connection::class);
        self::clearResolvedInstance(SolanaRpcClient::class);

        self::$activeFake = $stub;

        return $stub;
    }

    /**
     * Return the active fake stub, or null if {@see fake()} has not been
     * invoked.
     */
    public static function fakedStub(): ?InMemoryRpcStub
    {
        return self::$activeFake;
    }

    /**
     * Drop the active fake binding. Useful in test tear-down so a
     * stubbed `SolanaRpcClient` does not leak into the next test.
     */
    public static function clearFake(): void
    {
        self::$activeFake = null;

        $app = self::resolveContainer();
        if ($app instanceof Container) {
            self::forgetIfBound($app, SolanaRpcClient::class);

            // Restore any client the host had bound before fake() was
            // called so a fake/clear cycle is non-destructive.
            if (self::$priorBoundClient instanceof SolanaRpcClient) {
                $app->instance(SolanaRpcClient::class, self::$priorBoundClient);
            }
        }

        self::$priorBoundClient = null;

        self::clearResolvedInstance(Connection::class);
        self::clearResolvedInstance(SolanaRpcClient::class);
    }

    private static function resolveContainer(): ?Container
    {
        $app = self::getFacadeApplication();

        return $app instanceof Container ? $app : null;
    }

    /**
     * Snapshot the currently-resolved `SolanaRpcClient` so {@see clearFake()}
     * can restore it. Returns null when no shared instance has been
     * resolved yet, or when a fake is already active (preserve the
     * already-captured prior client).
     */
    private static function capturePriorClient(IlluminateContainer $app): ?SolanaRpcClient
    {
        if (self::$activeFake instanceof InMemoryRpcStub) {
            return self::$priorBoundClient;
        }

        if (! $app->resolved(SolanaRpcClient::class)) {
            return null;
        }

        $resolved = $app->make(SolanaRpcClient::class);

        return $resolved instanceof SolanaRpcClient ? $resolved : null;
    }

    /**
     * `forgetInstance()` lives on the concrete container, not the
     * {@see Container} contract — narrow before calling.
     */
    private static function forgetIfBound(Container $app, string $abstract): void
    {
        if ($app instanceof IlluminateContainer) {
            $app->forgetInstance($abstract);
        }
    }
}
