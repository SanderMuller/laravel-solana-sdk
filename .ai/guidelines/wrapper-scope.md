# Wrapper scope

This package is a **thin** Laravel adapter. The interesting code lives in
`sandermuller/solana-php-sdk`.

## Rules

- Bindings live in `LaravelSolanaSdkServiceProvider` only.
- Add new RPC capability by extending the core SDK's `Connection` and
  adding a `@method` phpdoc line on `Facades\Solana`. Do not implement RPC
  in this repo.
- New `solana:*` commands are fine if they are dev/debugging helpers and
  the underlying RPC method already exists on `Connection`.
- Keep `config/solana-sdk.php` small. Three keys is plenty.

## Network-dependent tests

None. Tests must run offline. If a behaviour cannot be verified offline
(e.g. `getBalance` round-trip), don't test it — verify by hand on
devnet.
