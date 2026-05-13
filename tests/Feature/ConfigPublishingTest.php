<?php declare(strict_types=1);

it('publishes the package config under the solana-sdk-config tag', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'solana-sdk-config'])->assertSuccessful();

    $target = config_path('solana-sdk.php');

    expect(file_exists($target))->toBeTrue();

    // Cleanup so subsequent runs are not affected.
    @unlink($target);
});

it('exposes the merged config with sensible defaults', function (): void {
    expect(config('solana-sdk.token_program_id'))->toBe('TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA')
        ->and(config('solana-sdk.commands.enabled'))
        ->toBeTrue();
});
