<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJob;

use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandJobId>
 */
final readonly class CommandJobIds implements IteratorAggregate
{
    /**
     * @var list<CommandJobId>
     */
    private array $timestamps;

    private function __construct(
        CommandJobId ...$timestamps
    ) {
        $this->timestamps = array_values($timestamps);
    }

    /**
     * @param array<CommandJobId> $timestamps
     */
    public static function fromArray(array $timestamps): self
    {
        return new self(...$timestamps);
    }

    public function getIterator(): Traversable
    {
        yield from $this->timestamps;
    }

    public function contain(CommandJobId $candidate): bool
    {
        foreach ($this->timestamps as $timestamp) {
            if ($timestamp->equals($candidate)) {
                return true;
            }
        }
        return false;
    }
}
