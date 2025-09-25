<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandDefinition;

use Closure;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandDefinition>
 */
final readonly class CommandDefinitions implements IteratorAggregate
{
    /**
     * @var list<CommandDefinition>
     */
    private array $commandDefinitions;

    private function __construct(
        CommandDefinition ...$commandDefinitions
    ) {
        $this->commandDefinitions = array_values($commandDefinitions);
    }

    /**
     * @param array<CommandDefinition> $commandDefinitions
     */
    public static function fromArray(array $commandDefinitions): self
    {
        return new self(...$commandDefinitions);
    }

    public function getIterator(): Traversable
    {
        yield from $this->commandDefinitions;
    }

    public function get(CommandDefinitionId $id): CommandDefinition
    {
        foreach ($this->commandDefinitions as $commandDefinition) {
            if ($commandDefinition->id->equals($id)) {
                return $commandDefinition;
            }
        }
        throw new InvalidArgumentException(sprintf('Command definition with id "%s" not found.', $id->value), 1758297768);
    }

    /**
     * @template T
     * @param Closure(CommandDefinition): T $callback
     * @return list<T>
     */
    public function map(Closure $callback): array
    {
        return array_map($callback, $this->commandDefinitions);
    }
}
