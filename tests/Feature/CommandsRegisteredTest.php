<?php declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

it('registers all bundled solana:* commands', function (): void {
    $names = array_keys(resolve(Kernel::class)->all());

    expect($names)->toContain(
        'solana:balance',
        'solana:airdrop',
        'solana:account',
        'solana:tx',
        'solana:health',
        'solana:tokens',
        'solana:fees',
    );
});
