<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandDefinition;

final readonly class CommandDefinitionOptions
{
    public function __construct(
        public int|null $timeout,
    ) {
    }

    public static function create(
        int|null $timeout = null,
    ): self {
        return new self(
            $timeout
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toSimpleArray(): array
    {
        return array_filter(get_object_vars($this), static fn ($v) => $v !== null);
    }
}
