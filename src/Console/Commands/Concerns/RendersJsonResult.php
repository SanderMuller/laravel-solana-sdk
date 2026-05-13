<?php declare(strict_types=1);

namespace SanderMuller\LaravelSolanaSdk\Console\Commands\Concerns;

use Illuminate\Console\Command;

/**
 * @phpstan-require-extends Command
 */
trait RendersJsonResult
{
    protected function renderJson(mixed $payload): void
    {
        $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function stringArgument(string $name): string
    {
        $value = $this->argument($name);

        return is_string($value) ? $value : '';
    }
}
