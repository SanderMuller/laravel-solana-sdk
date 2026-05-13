<?php declare(strict_types=1);

use SanderMuller\SolanaPhpSdk\Connection;

it('treats Connection::getBalance() as lamports and renders SOL via division', function (): void {
    // Stub Connection so we don't hit the network. getBalance returns the
    // raw RPC `value` field (lamports as float).
    $stub = new class extends Connection {
        public function getBalance(string $walletAddress): float
        {
            return 2_500_000_000.0; // 2.5 SOL
        }
    };

    app()->instance(Connection::class, $stub);

    $this->artisan('solana:balance', ['address' => 'FakeAddr'])
        ->expectsOutputToContain('Address:  FakeAddr')
        ->expectsOutputToContain('Balance:  2.500000000 SOL')
        ->expectsOutputToContain('Lamports: 2500000000')
        ->assertExitCode(0);
});

it('renders a zero-balance wallet as 0 SOL, 0 lamports', function (): void {
    $stub = new class extends Connection {
        public function getBalance(string $walletAddress): float
        {
            return 0.0;
        }
    };

    app()->instance(Connection::class, $stub);

    $this->artisan('solana:balance', ['address' => 'Empty'])
        ->expectsOutputToContain('Balance:  0.000000000 SOL')
        ->expectsOutputToContain('Lamports: 0')
        ->assertExitCode(0);
});
