<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandJobRepository;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Webmozart\Assert\Assert;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandJob\CommandJob;
use wwwision\commandJobs\commandJob\CommandJobs;
use wwwision\commandJobs\commandJob\CommandJobId;

final readonly class YamlCommandJobRepository implements CommandJobRepository
{
    public function __construct(
        private string $yamlFilePath,
    ) {

        if (!file_exists($this->yamlFilePath)) {
            file_put_contents($this->yamlFilePath, '[]');
        }
    }

    public function getAll(): CommandJobs
    {
        $items = [];
        foreach ($this->loadEntries() as $id => $definitionId) {
            $items[] = new CommandJob(
                CommandJobId::fromString((string)$id),
                CommandDefinitionId::fromString($definitionId),
            );
        }
        return CommandJobs::fromArray($items);
    }

    public function add(CommandJob $commandJob): void
    {
        $entries = $this->loadEntries();
        $entries[$commandJob->id->value] = $commandJob->commandDefinitionId->value;
        file_put_contents($this->yamlFilePath, Yaml::dump($entries));
    }

    /**
     * @return array<int|string, string>
     */
    private function loadEntries(): array
    {
        try {
            $result = Yaml::parseFile($this->yamlFilePath);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Failed to load command jobs from "%s": %s', $this->yamlFilePath, $e->getMessage()), 1758202300, $e);
        }
        Assert::isArray($result);
        return $result;
    }
}
