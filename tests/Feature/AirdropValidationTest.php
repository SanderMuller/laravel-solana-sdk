<?php declare(strict_types=1);

it('rejects a non-positive SOL amount', function (): void {
    $this->artisan('solana:airdrop', ['address' => 'FakeAddr', 'sol' => '0'])
        ->expectsOutputToContain('SOL amount must be positive.')
        ->assertExitCode(2);
});

it('rejects a negative SOL amount', function (): void {
    $this->artisan('solana:airdrop', ['address' => 'FakeAddr', 'sol' => '-1'])
        ->expectsOutputToContain('SOL amount must be positive.')
        ->assertExitCode(2);
});
