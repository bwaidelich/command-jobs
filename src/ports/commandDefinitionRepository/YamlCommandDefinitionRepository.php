<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandDefinitionRepository;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Webmozart\Assert\Assert;
use wwwision\commandJobs\commandDefinition\CommandDefinition;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandDefinition\CommandDefinitions;
use wwwision\commandJobs\commandDefinition\CommandWithArguments;

final readonly class YamlCommandDefinitionRepository implements CommandDefinitionRepository
{
    public function __construct(
        private string $yamlFilePath,
    ) {

        if (!file_exists($this->yamlFilePath)) {
            file_put_contents($this->yamlFilePath, '[]');
        }
    }

    public function getAll(): CommandDefinitions
    {
        $definitions = [];
        foreach ($this->loadDefinitions() as $id => $definition) {
            $definitions[] = self::convertDefinition($id, $definition);
        }
        return CommandDefinitions::fromArray($definitions);
    }

    public function findById(CommandDefinitionId $id): CommandDefinition|null
    {
        foreach ($this->loadDefinitions() as $definitionId => $definition) {
            if ($definitionId === $id->value) {
                return self::convertDefinition($definitionId, $definition);
            }
        }
        return null;
    }

    public function add(CommandDefinition $commandDefinition): void
    {
        $definitions = $this->loadDefinitions();
        $definitions[$commandDefinition->id->value] = [
            'description' => $commandDefinition->description,
            'cmd' => $commandDefinition->cmd->toArray(),
        ];
        file_put_contents($this->yamlFilePath, Yaml::dump($definitions));
    }

    /**
     * @param array<string, mixed> $definition
     */
    private static function convertDefinition(string $id, array $definition): CommandDefinition
    {
        Assert::string($definition['description']);
        Assert::isList($definition['cmd']);
        return new CommandDefinition(
            CommandDefinitionId::fromString($id),
            $definition['description'],
            CommandWithArguments::fromParts($definition['cmd']),
        );
    }

    /**
     * @return array<string, array<mixed>>
     */
    private function loadDefinitions(): array
    {
        try {
            $result = Yaml::parseFile($this->yamlFilePath);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Failed to load command definitions from "%s": %s', $this->yamlFilePath, $e->getMessage()), 1758202300, $e);
        }
        Assert::isMap($result);
        Assert::allIsArray($result);
        return $result;
    }
}
