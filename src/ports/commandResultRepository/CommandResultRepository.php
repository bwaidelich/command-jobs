<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandResultRepository;

use wwwision\commandJobs\commandResult\CommandResult;
use wwwision\commandJobs\commandResult\CommandResults;

interface CommandResultRepository
{
    public function getAll(): CommandResults;

    public function add(CommandResult $commandResult): void;
}
