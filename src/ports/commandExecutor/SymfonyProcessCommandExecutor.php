<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandExecutor;

use RuntimeException;
use Symfony\Component\Process\Process;
use wwwision\commandJobs\commandDefinition\CommandWithArguments;

final readonly class SymfonyProcessCommandExecutor implements CommandExecutor
{
    public function run(CommandWithArguments $command): string
    {
        $process = new Process($command->toArray());
        $process->run(function ($type, $buffer) {
            if ($type === Process::ERR) {
                throw new RuntimeException($buffer);
            }
        });
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
        return $process->getOutput();
    }
}
