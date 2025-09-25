<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJobsEvent;

use wwwision\commandJobs\commandResult\CommandResults;

final readonly class RunEnded implements CommandJobsEvent
{

    public function __construct(
        public CommandResults $commandResults,
    ) {
    }

    public function severity(): CommandJobsEventSeverity
    {
        return $this->commandResults->hasFailed() ? CommandJobsEventSeverity::ERROR : CommandJobsEventSeverity::INFO;
    }

    public function __toString(): string
    {
        $numberOfResults = $this->commandResults->count();
        if ($numberOfResults === 0) {
            return 'run ended (no pending commands)';
        }
        $numberOfErrors = $this->commandResults->onlyFailed()->count();
        return sprintf('run ended (%d succeeded, %d failed)', $numberOfResults - $numberOfErrors, $numberOfErrors);
    }
}
