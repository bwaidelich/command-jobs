<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandJobRepository;

use wwwision\commandJobs\commandJob\CommandJob;
use wwwision\commandJobs\commandJob\CommandJobs;

interface CommandJobRepository
{
    public function getAll(): CommandJobs;

    public function add(CommandJob $commandJob): void;
}
