<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandResultRepository;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Webmozart\Assert\Assert;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandJob\CommandJobId;
use wwwision\commandJobs\commandResult\CommandResult;
use wwwision\commandJobs\commandResult\CommandResults;

final readonly class YamlCommandResultRepository implements CommandResultRepository
{
    public function __construct(
        private string $yamlFilePath,
    ) {

        if (!file_exists($this->yamlFilePath)) {
            file_put_contents($this->yamlFilePath, '[]');
        }
    }

    public function getAll(): CommandResults
    {
        $results = [];
        foreach ($this->loadResults() as $result) {
            $results[] = self::convertResult($result);
        }
        return CommandResults::fromArray($results);
    }

    public function add(CommandResult $commandResult): void
    {
        $results = $this->loadResults();
        $results[] = [
            'commandJobId' => $commandResult->commandJobId->value,
            'commandDefinitionId' => $commandResult->commandDefinitionId->value,
            'executionTime' => $commandResult->executionTime->format(DATE_ATOM),
            'executionDurationInMilliseconds' => $commandResult->executionDurationInMilliseconds,
            'success' => $commandResult->success,
            'output' => $commandResult->output,
        ];
        file_put_contents($this->yamlFilePath, Yaml::dump($results));
    }

    /**
     * @param array<mixed> $result
     */
    private static function convertResult(array $result): CommandResult
    {
        Assert::string($result['commandJobId']);
        Assert::string($result['commandDefinitionId']);
        Assert::string($result['executionTime']);
        $executionTime = DateTimeImmutable::createFromFormat(DATE_ATOM, $result['executionTime']);
        Assert::isInstanceOf($executionTime, DateTimeImmutable::class);
        Assert::numeric($result['executionDurationInMilliseconds']);
        Assert::boolean($result['success']);
        Assert::string($result['output']);
        return new CommandResult(
            commandJobId: CommandJobId::fromString($result['commandJobId']),
            commandDefinitionId: CommandDefinitionId::fromString($result['commandDefinitionId']),
            executionTime: $executionTime,
            executionDurationInMilliseconds: (int)$result['executionDurationInMilliseconds'],
            success: $result['success'],
            output: $result['output'],
        );
    }

    /**
     * @return list<array<mixed>>
     */
    private function loadResults(): array
    {
        try {
            $result = Yaml::parseFile($this->yamlFilePath);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Failed to load command definitions from "%s": %s', $this->yamlFilePath, $e->getMessage()), 1758202300, $e);
        }
        Assert::isList($result);
        return $result;
    }
}
