<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandDefinitionRepository;

use wwwision\commandJobs\commandDefinition\CommandDefinition;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandDefinition\CommandDefinitions;

interface CommandDefinitionRepository
{
    public function getAll(): CommandDefinitions;

    public function findById(CommandDefinitionId $id): CommandDefinition|null;

    public function add(CommandDefinition $commandDefinition): void;
}
