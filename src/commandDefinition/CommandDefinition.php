<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandDefinition;

final readonly class CommandDefinition
{
    public function __construct(
        public CommandDefinitionId $id,
        public string $description,
        public CommandWithArguments $cmd,
        public CommandDefinitionOptions $options,
    ) {
    }
}
