# Changelog

All notable changes to `laravel-solana-sdk` are documented here. This file is
updated automatically by CI on release publish — do not edit by hand.

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
