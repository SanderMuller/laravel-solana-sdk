<?php declare(strict_types=1);

it('ships token_program_id as the official SPL Token program', function (): void {
    expect(config('solana-sdk.token_program_id'))
        ->toBe('TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA');
});

it('ships commands.enabled as true by default', function (): void {
    expect(config('solana-sdk.commands.enabled'))->toBeTrue();
});

it('allows overriding token_program_id at runtime', function (): void {
    config()->set('solana-sdk.token_program_id', 'TokenzQdBNbLqP5VEhdkAS6EPFLC1PHnBqCXEpPxuEb');

    expect(config('solana-sdk.token_program_id'))
        ->toBe('TokenzQdBNbLqP5VEhdkAS6EPFLC1PHnBqCXEpPxuEb');
});

it('does not preload solana config when no env vars are set (mainnet default)', function (): void {
    // Confirm the config file's env() fallback chain — the wrapper test env
    // overrides this to devnet, so we re-read the raw file to verify the
    // shipped default.
    $shipped = require __DIR__ . '/../../config/solana-sdk.php';

    expect($shipped['network'])->toBe('mainnet');
});
