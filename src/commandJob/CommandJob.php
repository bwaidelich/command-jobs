<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJob;

use wwwision\commandJobs\commandDefinition\CommandDefinitionId;

final readonly class CommandJob
{
    public function __construct(
        public CommandJobId $id,
        public CommandDefinitionId $commandDefinitionId,
    ) {
    }
}
