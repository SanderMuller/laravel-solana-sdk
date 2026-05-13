<?php declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use SanderMuller\LaravelSolanaSdk\Tests\Support\DisabledCommandsTestCase;

uses(DisabledCommandsTestCase::class);

it('hides solana:* commands when solana-sdk.commands.enabled is false', function (): void {
    $names = array_keys(resolve(Kernel::class)->all());

    expect($names)->not->toContain(
        'solana:balance',
        'solana:airdrop',
        'solana:account',
        'solana:tx',
        'solana:health',
        'solana:tokens',
        'solana:fees',
    );
});
