<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandResult;

use Closure;
use Countable;
use IteratorAggregate;
use Traversable;
use wwwision\commandJobs\commandJob\CommandJobIds;

/**
 * @implements IteratorAggregate<CommandResult>
 */
final readonly class CommandResults implements IteratorAggregate, Countable
{
    /**
     * @var list<CommandResult>
     */
    private array $commandResults;

    private bool $hasFailed;

    private function __construct(
        CommandResult ...$commandResults
    ) {
        $hasFailed = false;
        foreach ($commandResults as $commandResult) {
            if ($commandResult->success === false) {
                $hasFailed = true;
                break;
            }
        }
        $this->hasFailed = $hasFailed;
        $this->commandResults = array_values($commandResults);
    }

    /**
     * @param array<CommandResult> $commandResults
     */
    public static function fromArray(array $commandResults): self
    {
        return new self(...$commandResults);
    }

    public function getIterator(): Traversable
    {
        yield from $this->commandResults;
    }

    /**
     * @template T
     * @param Closure(CommandResult): T $callback
     * @return list<T>
     */
    public function map(Closure $callback): array
    {
        return array_map($callback, $this->commandResults);
    }

    /**
     * @param Closure(CommandResult): bool $callback
     */
    public function filter(Closure $callback): self
    {
        return self::fromArray(array_filter($this->commandResults, $callback));
    }

    public function getCommandJobIds(): CommandJobIds
    {
        return CommandJobIds::fromArray($this->map(static fn (CommandResult $result) => $result->commandJobId));
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }

    public function onlyFailed(): self
    {
        return $this->filter(static fn (CommandResult $result) => $result->success === false);
    }

    public function withoutFailed(): self
    {
        return $this->filter(static fn (CommandResult $result) => $result->success === true);
    }

    public function count(): int
    {
        return count($this->commandResults);
    }
}
