<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJob;

use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandJob>
 */
final readonly class CommandJobs implements IteratorAggregate, Countable
{
    /**
     * @var list<CommandJob>
     */
    private array $commandJobs;

    private function __construct(
        CommandJob ...$commandJobs
    ) {
        $this->commandJobs = array_values($commandJobs);
    }

    /**
     * @param array<CommandJob> $commandJobs
     */
    public static function fromArray(array $commandJobs): self
    {
        return new self(...$commandJobs);
    }

    public function getIterator(): Traversable
    {
        yield from $this->commandJobs;
    }

    /**
     * @param Closure(CommandJob): bool $callback
     */
    public function filter(Closure $callback): self
    {
        return self::fromArray(array_filter($this->commandJobs, $callback));
    }

    public function count(): int
    {
        return count($this->commandJobs);
    }
}
