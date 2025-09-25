<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandResult;

use DateTimeImmutable;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandJob\CommandJobId;

final readonly class CommandResult
{
    public function __construct(
        public CommandJobId $commandJobId,
        public CommandDefinitionId $commandDefinitionId,
        public DateTimeImmutable $executionTime,
        public int $executionDurationInMilliseconds,
        public bool $success,
        public string $output,
    ) {
    }
}
