<?php declare(strict_types=1);

use DG\BypassFinals;
use SanderMuller\LaravelSolanaSdk\Facades\Solana;
use SanderMuller\LaravelSolanaSdk\Tests\TestCase;

BypassFinals::enable();

pest()->extend(TestCase::class)->in(__DIR__ . '/Feature', __DIR__ . '/Unit');

// Static facade state survives between tests in the same process — reset
// after each test so a forgotten clearFake() in one file cannot leak the
// stub binding into another.
afterEach(function (): void {
    Solana::clearFake();
});
