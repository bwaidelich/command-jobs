<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandExecutor;

use wwwision\commandJobs\commandDefinition\CommandWithArguments;

interface CommandExecutor
{
    public function run(CommandWithArguments $command): string;
}
