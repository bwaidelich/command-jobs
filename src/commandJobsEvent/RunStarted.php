<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJobsEvent;

use wwwision\commandJobs\commandJob\CommandJobs;

final readonly class RunStarted implements CommandJobsEvent
{
    public function __construct(
        public CommandJobs $commandJobs,
    ) {
    }

    public function severity(): CommandJobsEventSeverity
    {
        return CommandJobsEventSeverity::DEBUG;
    }

    public function __toString(): string
    {
        return sprintf('run started (%d command%s)', $this->commandJobs->count(), $this->commandJobs->count() === 1 ? '' : 's');
    }
}
