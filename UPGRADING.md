# Upgrading

## Pre-1.0 development

The package tracks `sandermuller/solana-php-sdk` `dev-main` via a
`vcs` repository entry until the core SDK has a tagged stable release.
Once core ships `^0.1`, switch the constraint in `composer.json` from
`"sandermuller/solana-php-sdk": "dev-main"` to the pinned tag and drop
the `repositories` + `minimum-stability: dev` entries.

## Per-release migration steps

### Unreleased

- **New `transport` config key.** Publish + diff `config/solana-sdk.php`
  if you've customised it locally — the wrapper now ships a `transport`
  array. Leaving the `urls` entry empty preserves the legacy single
  public-endpoint behaviour, so this change is non-breaking unless you
  set `SOLANA_RPC_URL` (then traffic routes through your provider with
  built-in retry + fallback).
- **`SolanaPubSubClient` is now container-resolvable.** Typehint it
  directly in jobs / controllers; do not `new` it manually if you want
  the configured-network defaults.
- **`Solana::fake()` is preferred over hand-rolled mocks.** Existing
  suites that bound `SolanaRpcClient` to a hand-rolled fake keep
  working, but new tests should use the facade helper for forward
  compatibility.
