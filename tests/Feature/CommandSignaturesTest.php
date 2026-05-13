<?php declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Command\Command;

dataset('command_signatures', [
    'solana:balance' => ['solana:balance', ['address']],
    'solana:airdrop' => ['solana:airdrop', ['address', 'sol']],
    'solana:account' => ['solana:account', ['address']],
    'solana:tx' => ['solana:tx', ['signature']],
    'solana:health' => ['solana:health', []],
    'solana:tokens' => ['solana:tokens', ['owner']],
    'solana:fees' => ['solana:fees', ['addresses']],
]);

it('registers each bundled command with the expected argument names', function (string $name, array $expectedArgs): void {
    $all = resolve(Kernel::class)->all();
    $command = $all[$name] ?? null;

    assert($command instanceof Command, "command {$name} not registered");

    $defined = array_keys($command->getDefinition()->getArguments());
    // Symfony adds an implicit "command" argument we don't care about here.
    $userDefined = array_values(array_filter($defined, static fn (string $a): bool => $a !== 'command'));

    expect($userDefined)->toBe($expectedArgs);
})->with('command_signatures');

it('describes each bundled command with non-empty help text', function (): void {
    $all = resolve(Kernel::class)->all();

    foreach (['solana:balance', 'solana:airdrop', 'solana:account', 'solana:tx', 'solana:health', 'solana:tokens', 'solana:fees'] as $name) {
        expect($all[$name]->getDescription())->not->toBeEmpty();
    }
});
