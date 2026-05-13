# Changelog

All notable changes to `laravel-solana-sdk` are documented here. This file is
updated automatically by CI on release publish — do not edit by hand.

## v0.1.0 - 2026-05-13

### Highlights

- **`Solana` + `SolanaRpc` facades** — `Solana` proxies the typed `Connection` API (~60 methods covering ~80 % of the Solana JSON-RPC spec: accounts, blocks, signatures, tokens, supply, stake, vote, inflation); `SolanaRpc` is the raw `SolanaRpcClient` escape hatch for anything the typed facade doesn't yet cover.
- **Multi-endpoint transport from env** — `SOLANA_RPC_URL` + `SOLANA_RPC_URL_FALLBACK` + `SOLANA_TRANSPORT_MODE` (`fallback` / `round_robin`) + retry knobs route traffic through your provider (Helius, Triton, …) with built-in retry-then-fallback. Leave the URLs unset to keep the public-endpoint default.
- **`SolanaPubSubClient` container binding** — typehint it in a job or controller and get a transient WebSocket client pinned to the configured network.
- **`Solana::fake()` for tests** — swaps the bound `SolanaRpcClient` for the core SDK's `InMemoryRpcStub` so suites can script JSON-RPC responses and assert call sequences without touching the network. Pair with `PestExpectations::register()` from the core SDK for `toBeConfirmed` / `toHaveCustomCode` / `toBeInstructionError` matchers.
- **`solana:*` artisan commands** — `solana:balance`, `solana:airdrop`, `solana:account`, `solana:tx`, `solana:health`, `solana:tokens`, `solana:fees` for local debugging against devnet/testnet. Disable in production with `SOLANA_COMMANDS_ENABLED=false`.

### Compatibility

- PHP `^8.3`
- Laravel `^11.0 || ^12.0 || ^13.0`
- CI matrix: PHP 8.3/8.4 × Laravel 11/12/13 × `prefer-lowest` / `prefer-stable`

### Install

```bash
composer require sandermuller/laravel-solana-sdk
php artisan vendor:publish --tag=solana-sdk-config   # optional

```
See the [README](README.md) for facade examples, transport configuration, and the queue-based confirmation flow.

**Full Changelog**: https://github.com/SanderMuller/laravel-solana-sdk/commits/0.1.0

## Unreleased

### Added

- `transport` config block — env-driven multi-endpoint stack
  (`SOLANA_RPC_URL`, `SOLANA_RPC_URL_FALLBACK`, `SOLANA_TRANSPORT_MODE`,
  retry knobs). The service provider hands the array to the core SDK's
  `Rpc\TransportFactory` so a Laravel app gets fallback / round-robin /
  retry transports without touching the container directly.
- `SolanaPubSubClient` transient binding pinned to the configured network.
- `Solana::fake()` / `Solana::fakedStub()` / `Solana::clearFake()` —
  Laravel-flavoured helpers that wire the core SDK's
  `Testing\InMemoryRpcStub` through the app container.
- Expanded `Solana` facade `@method` catalogue covering the new typed
  helpers added in the core SDK (`accountInfo`, `multipleAccounts`,
  `latestBlockhash`, `programAccounts`, `sendAndConfirmTransaction`,
  `sendAndConfirmVersionedTransaction`, `confirmTransaction`, cluster
  info, blocks, signatures, tokens, supply, stake, vote, inflation).

### Changed

- `config/solana-sdk.php` published key set grew from three to four
  knobs (added `transport`).

## 0.0.x

- Initial scaffold: service provider, `Solana` + `SolanaRpc` facades,
  env-driven config, and `solana:*` artisan commands wrapping
  `sandermuller/solana-php-sdk`.
