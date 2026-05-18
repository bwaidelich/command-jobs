<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandResultRepository;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use RuntimeException;
use Webmozart\Assert\Assert;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandJob\CommandJobId;
use wwwision\commandJobs\commandResult\CommandResult;
use wwwision\commandJobs\commandResult\CommandResults;

final readonly class DbalCommandResultRepository implements CommandResultRepository
{
    public function __construct(
        private Connection $connection,
        private string $tableName,
    ) {
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        try {
            $schemaManager = $this->connection->createSchemaManager();
            if ($schemaManager->tablesExist([$this->tableName])) {
                return;
            }
            $table = new Table($this->tableName);
            $table->addColumn('command_job_id', Types::STRING, ['length' => 14, 'notnull' => true]);
            $table->addColumn('command_definition_id', Types::STRING, ['length' => 255, 'notnull' => true]);
            $table->addColumn('execution_time', Types::DATETIME_IMMUTABLE, ['notnull' => true]);
            $table->addColumn('execution_duration_in_milliseconds', Types::INTEGER, ['notnull' => true]);
            $table->addColumn('success', Types::BOOLEAN, ['notnull' => true]);
            $table->addColumn('output', Types::TEXT, ['notnull' => true]);
            $table->addIndex(['command_job_id', 'command_definition_id']);
            $schemaManager->createTable($table);
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to create table %s: %s', $this->tableName, $e->getMessage()), 1759142550, $e);
        }
    }

    public function getAll(): CommandResults
    {
        try {
            $rows = $this->connection->fetchAllAssociative('SELECT * FROM ' . $this->connection->quoteIdentifier($this->tableName));
            $platform = $this->connection->getDatabasePlatform();
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to obtain command results from database: %s', $e->getMessage()), 1759142740, $e);
        }
        return CommandResults::fromArray(array_map(static fn (array $row) => self::convertResult($row, $platform), $rows));
    }

    public function add(CommandResult $commandResult): void
    {
        try {
            $this->connection->insert($this->tableName, [
                'command_job_id' => $commandResult->commandJobId->value,
                'command_definition_id' => $commandResult->commandDefinitionId->value,
                'execution_time' => $commandResult->executionTime,
                'execution_duration_in_milliseconds' => $commandResult->executionDurationInMilliseconds,
                'success' => $commandResult->success,
                'output' => $commandResult->output,
            ], [
                'execution_time' => Types::DATETIME_IMMUTABLE,
                'success' => Types::BOOLEAN,
            ]);
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to save command result to database: %s', $e->getMessage()), 1759142755, $e);
        }
    }

    /**
     * @param array<mixed> $result
     */
    private static function convertResult(array $result, AbstractPlatform $platform): CommandResult
    {
        Assert::string($result['command_job_id']);
        Assert::string($result['command_definition_id']);
        Assert::string($result['execution_time']);
        $executionTime = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($result['execution_time'], $platform);
        Assert::isInstanceOf($executionTime, DateTimeImmutable::class);
        Assert::numeric($result['execution_duration_in_milliseconds']);
        $success = Type::getType(Types::BOOLEAN)->convertToPHPValue($result['success'], $platform);
        Assert::boolean($success);
        Assert::string($result['output']);
        return new CommandResult(
            commandJobId: CommandJobId::fromString($result['command_job_id']),
            commandDefinitionId: CommandDefinitionId::fromString($result['command_definition_id']),
            executionTime: $executionTime,
            executionDurationInMilliseconds: (int)$result['execution_duration_in_milliseconds'],
            success: $success,
            output: $result['output'],
        );
    }
}
