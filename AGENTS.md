# CLAUDE.md — laravel-solana-sdk

This package is a thin Laravel wrapper around `sandermuller/solana-php-sdk`.

## Scope

- Service provider binds `SolanaRpcClient` (singleton, network from config)
  and `Connection` (transient).
- Two facades: `Solana` → `Connection`, `SolanaRpc` → `SolanaRpcClient`.
- A small set of `solana:*` artisan commands for local debugging.

The core SDK already ships its own `ServiceProvider` + `Bootstrap` +
`Config` (static loader). This wrapper does NOT replace those — it just
provides a Laravel-native experience (env-driven config, container
singletons via `$this->app`, facades, command registration).

## Boundaries

- Don't reimplement RPC methods here. If a method is missing, extend the
  core SDK and add a `@method` phpdoc line to the `Solana` facade.
- Don't add network-dependent tests. Local tests must work offline.
- Don't add new dependencies. The wrapper exists to be tiny.

## Tooling

PHPStan max + type_coverage 100. Pest + Orchestra Testbench. Pint preset
laravel with declare_strict_types. Rector with Laravel + Pest sets. Cache
dirs all under `.cache/`.

## Verification before completion

```bash
vendor/bin/pest          # tests
vendor/bin/pint --test   # style
vendor/bin/phpstan       # static
vendor/bin/rector --dry-run
```

<package-boost-guidelines>
# Package Boost Guidelines

These guidelines replace Laravel Boost's default foundation for
repositories that ship as Composer packages — Laravel-targeted or
framework-agnostic. The framing, tooling, and trade-offs differ from
application development; follow this version when working inside a
package codebase.

## Foundational Context

This codebase is a **Composer package**, not an application. The rules
below hold regardless of which framework (if any) the package targets.

- There is no `app/`, `bootstrap/`, `routes/`, `.env`, or database by
  default. Tooling that assumes an application context (e.g. running
  `php artisan` against the package itself) does not apply.
- The primary artefact is the package's public API — entry-point
  classes, service providers, exposed contracts. Everything else is
  scaffolding.
- Downstream consumers depend on this package via Composer. Every
  public change is a user-facing API change governed by semver.
- `composer.json` is the source of truth for supported PHP versions
  and any framework constraints. Check `require.php` (and any
  `require.<framework>/*` entries) before using version-specific
  features.

## Source Layout

- `src/` — package source, PSR-4 autoloaded per `composer.json`
- `tests/` — Pest or PHPUnit suite
- `config/` — publishable defaults shipped with the package, when
  applicable
- `resources/` — views, translations, Boost skills / guidelines, when
  applicable
- `database/migrations`, `database/factories` — only if the package
  ships them
- `workbench/` — developer-only Testbench scaffolding when Testbench
  is in use; never shipped

Check sibling files before inventing structure. Do not introduce new
top-level directories without a clear reason.

## Tests Are the Specification

The package has no running application to click through. Tests are how
behaviour is pinned down.

- Write tests alongside any behavioural change.
- Do not create "verification scripts" when a test can prove the same
  thing.
- Run the project's configured test runner (`vendor/bin/pest` or
  `vendor/bin/phpunit`) before claiming a change is done.

## Public API Discipline

- Every `public`, `protected`, or exported symbol is part of the
  package's surface. Breaking changes require a major version bump.
- Prefer `final` classes and `private`/`@internal` markers for
  anything not intended for extension.
- Keep config keys, published asset paths, and service container
  bindings stable across patch and minor versions.

## Conventions

- Match existing code style, naming, and structural patterns — check
  sibling files before writing new ones.
- Use descriptive names (`resolvePublishDestination`, not `resolve()`).
- Reuse existing helpers before adding new ones.
- Do not add dependencies without approval; every new `require` is a
  constraint downstream consumers inherit.

## Documentation Files

Only create or edit documentation (README, CHANGELOG, docs/) when
explicitly requested or when a behaviour change requires it.

## Replies

Be concise. Focus on what changed and why. Skip restating what the
diff already shows.

## If your package targets Laravel

The rest of this document is Laravel-specific. Skip it if the package
is framework-agnostic — `composer.json` should make that obvious (no
`require.illuminate/*`, no `require.laravel/framework`).

### Laravel context

A Testbench-provided Laravel application is spun up only at test
time. Base test case is `Orchestra\Testbench\TestCase`.
`composer.json`'s `require.illuminate/*` (or
`require.laravel/framework`) defines the supported Laravel range —
check it before using version-specific framework APIs.

### Use `vendor/bin/testbench`, not `php artisan`

Running artisan commands directly against the package fails — there is
no host application. Use Testbench's binary:

| Instead of | Use |
|---|---|
| `php artisan test` | `vendor/bin/pest` or `vendor/bin/phpunit` |
| `php artisan tinker` | `vendor/bin/testbench tinker` |
| `php artisan make:*` | Create files manually under `src/` |
| `php artisan vendor:publish` | `vendor/bin/testbench vendor:publish` |

#### Commands that require `laravel/boost`

These only apply when the package has `laravel/boost` as a dev
dependency. Skip if Boost isn't installed — `boost sync`
prints a warning and moves on.

| Instead of | Use |
|---|---|
| `php artisan boost:install` | `vendor/bin/testbench boost:install` |
| `php artisan boost:mcp` | `vendor/bin/testbench boost:mcp` |

Register the package's service provider in `testbench.yaml` under
`providers:` so Testbench boots it. Published files land in
`workbench/` by default, not `config/` or `resources/` of a host app.

### Cross-Version Compatibility

Supporting multiple Laravel / PHP majors is routine for Laravel
packages. Activate `cross-version-laravel-support` **before** writing
the code; activate `ci-matrix-troubleshooting` **after** a matrix cell
has failed.

---

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
</package-boost-guidelines>
