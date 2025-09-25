<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJobsEvent;

use wwwision\commandJobs\commandDefinition\CommandDefinition;
use wwwision\commandJobs\commandResult\CommandResult;

final readonly class CommandExecuted implements CommandJobsEvent
{
    public function __construct(
        public CommandResult $commandResult,
        public CommandDefinition $commandDefinition,
    ) {
    }

    public function severity(): CommandJobsEventSeverity
    {
        return $this->commandResult->success ? CommandJobsEventSeverity::DEBUG : CommandJobsEventSeverity::ERROR;
    }

    public function __toString(): string
    {
        $truncatedOutput = str_replace("\n", '\\n', trim($this->commandResult->output));
        if (strlen($truncatedOutput) > 100) {
            $truncatedOutput = substr($truncatedOutput, 0, 100) . '...';
        }
        if ($this->commandResult->success) {
            return sprintf('Successfully ran job "%s" (definition: "%s", cmd: %s): %s', $this->commandResult->commandJobId->value, $this->commandResult->commandDefinitionId->value, $this->commandDefinition->cmd->toString(), $truncatedOutput);
        }
        return sprintf('Failed to run job "%s" (definition: "%s", cmd: %s): %s', $this->commandResult->commandJobId->value, $this->commandResult->commandDefinitionId->value, $this->commandDefinition->cmd->toString(), $truncatedOutput);
    }
}
