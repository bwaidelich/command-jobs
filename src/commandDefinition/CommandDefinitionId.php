<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandDefinition;

final readonly class CommandDefinitionId
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $id): bool
    {
        return $this->value === $id->value;
    }
}
