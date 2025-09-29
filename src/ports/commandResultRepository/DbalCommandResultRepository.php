<?php

declare(strict_types=1);

namespace wwwision\commandJobs\ports\commandResultRepository;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use RuntimeException;
use Webmozart\Assert\Assert;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandJob\CommandJobId;
use wwwision\commandJobs\commandResult\CommandResult;
use wwwision\commandJobs\commandResult\CommandResults;

final readonly class DbalCommandResultRepository implements CommandResultRepository
{
    private const COLUMN_TYPES = [
        'execution_time' => Types::DATETIME_IMMUTABLE,
        'success' => Types::BOOLEAN,
    ];

    public function __construct(
        private Connection $connection,
        private string $tableName,
    ) {
        $this->verifyPlatform();
        $this->connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `$tableName` (
              `command_job_id` VARCHAR(14) NOT NULL,
              `command_definition_id` VARCHAR(255) NOT NULL,
              `execution_time` DATETIME NOT NULL,
              `execution_duration_in_milliseconds` INT(11) NOT NULL,
              `success` TINYINT(1) NOT NULL,
              `output` TEXT NOT NULL,
              KEY `command_job_id` (`command_job_id`,`command_definition_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL);
    }

    private function verifyPlatform(): void
    {
        try {
            $platform = $this->connection->getDatabasePlatform();
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to verify database platform: %s', $e->getMessage()), 1759142550, $e);
        }
        if (!$platform instanceof AbstractMySQLPlatform) {
            throw new RuntimeException('This adapter only supports MySQL/MariaDB compatible databases', 1759142553);
        }
    }

    public function getAll(): CommandResults
    {
        try {
            $rows = $this->connection->fetchAllAssociative('SELECT * FROM ' . $this->connection->quoteIdentifier($this->tableName));
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to obtain command results from database: %s', $e->getMessage()), 1759142740, $e);
        }
        return CommandResults::fromArray(array_map(self::convertResult(...), $rows));
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
            ], self::COLUMN_TYPES);
        } catch (DbalException $e) {
            throw new RuntimeException(sprintf('Failed to save command result to database: %s', $e->getMessage()), 1759142755, $e);
        }
    }

    /**
     * @param array<mixed> $result
     */
    private static function convertResult(array $result): CommandResult
    {
        Assert::string($result['command_job_id']);
        Assert::string($result['command_definition_id']);
        Assert::string($result['execution_time']);
        $executionTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result['execution_time']);
        Assert::isInstanceOf($executionTime, DateTimeImmutable::class);
        Assert::numeric($result['execution_duration_in_milliseconds']);
        Assert::numeric($result['success']);
        Assert::string($result['output']);
        return new CommandResult(
            commandJobId: CommandJobId::fromString($result['command_job_id']),
            commandDefinitionId: CommandDefinitionId::fromString($result['command_definition_id']),
            executionTime: $executionTime,
            executionDurationInMilliseconds: (int)$result['execution_duration_in_milliseconds'],
            success: (bool)$result['success'],
            output: $result['output'],
        );
    }
}
