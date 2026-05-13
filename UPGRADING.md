# Upgrading

## Pre-release tasks before tagging v0.1.0

The package is currently in `v0.x` development against an unreleased
`sandermuller/solana-php-sdk` checkout. Before publishing to Packagist:

1. **Tag `sandermuller/solana-php-sdk`** (e.g. `0.1.0`) and publish it to
   Packagist.
2. **Pin the core SDK constraint** in `composer.json` to that tag, e.g.
   `"sandermuller/solana-php-sdk": "^0.1"` instead of `"*@dev"`.
3. **Remove the `repositories` block** from `composer.json` (the local
   `path` repository to `../solana-php-sdk` is only honoured when this
   package is the root project — downstream consumers do not inherit
   it, so leaving it in the published manifest is dead config and
   confusing for contributors who don't have the sibling checkout).
4. **Update `composer.json` minimum-stability** if needed — `stable`
   should work once the core SDK has a tagged stable release.

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
