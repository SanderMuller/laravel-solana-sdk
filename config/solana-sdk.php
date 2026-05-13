<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Network
    |--------------------------------------------------------------------------
    |
    | Which Solana cluster the bound SolanaRpcClient targets. One of
    | Network::MAINNET, Network::TESTNET, Network::DEVNET. Override via
    | SOLANA_NETWORK env var ("mainnet", "testnet", "devnet").
    |
    */
    'network' => env('SOLANA_NETWORK', 'mainnet'),

    /*
    |--------------------------------------------------------------------------
    | Token Program ID
    |--------------------------------------------------------------------------
    |
    | Default SPL Token program ID used by program-helper classes.
    |
    */
    'token_program_id' => env(
        'SOLANA_TOKEN_PROGRAM_ID',
        'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA',
    ),

    /*
    |--------------------------------------------------------------------------
    | RPC Transport
    |--------------------------------------------------------------------------
    |
    | By default the SDK posts JSON-RPC requests to the configured network's
    | public endpoint. For production — primary provider (Helius, Triton, …)
    | plus one or two fallbacks, custom headers, retry — supply a transport
    | array. `mode` is `fallback` (default) or `round_robin`. Retry is
    | applied per-endpoint before fallback advances. Leave `urls` empty or
    | set the whole array to `null` to keep the single-endpoint default.
    |
    */
    'transport' => [
        'mode' => env('SOLANA_TRANSPORT_MODE', 'fallback'),
        'urls' => array_values(array_filter([
            env('SOLANA_RPC_URL'),
            env('SOLANA_RPC_URL_FALLBACK'),
        ])),
        'headers' => [],
        'timeout' => (float) env('SOLANA_RPC_TIMEOUT', 30.0),
        'retry' => [
            'max_attempts' => (int) env('SOLANA_RPC_RETRY_ATTEMPTS', 3),
            'base_delay_ms' => (int) env('SOLANA_RPC_RETRY_BASE_MS', 100),
            'max_delay_ms' => (int) env('SOLANA_RPC_RETRY_MAX_MS', 2_000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Toggle artisan command registration. Defaults to ON — the bundled
    | commands hit live RPC endpoints. Set `SOLANA_COMMANDS_ENABLED=false`
    | in production environments where you do not want operators to be
    | able to call mainnet RPC from the artisan shell.
    |
    */
    'commands' => [
        'enabled' => env('SOLANA_COMMANDS_ENABLED', true),
    ],
];
