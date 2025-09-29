<?php

declare(strict_types=1);

namespace wwwision\commandJobs;

use Closure;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use RuntimeException;
use Throwable;
use wwwision\commandJobs\commandDefinition\CommandDefinition;
use wwwision\commandJobs\commandDefinition\CommandDefinitionId;
use wwwision\commandJobs\commandDefinition\CommandWithArguments;
use wwwision\commandJobs\commandJob\CommandJob;
use wwwision\commandJobs\commandJob\CommandJobId;
use wwwision\commandJobs\commandJobsEvent\CommandExecuted;
use wwwision\commandJobs\commandJobsEvent\CommandJobsEvent;
use wwwision\commandJobs\commandJobsEvent\RunEnded;
use wwwision\commandJobs\commandJobsEvent\RunStarted;
use wwwision\commandJobs\commandResult\CommandResult;
use wwwision\commandJobs\commandResult\CommandResults;
use wwwision\commandJobs\ports\commandDefinitionRepository\CommandDefinitionRepository;
use wwwision\commandJobs\ports\commandExecutor\CommandExecutor;
use wwwision\commandJobs\ports\commandJobRepository\CommandJobRepository;
use wwwision\commandJobs\ports\commandResultRepository\CommandResultRepository;

final class CommandJobsApp
{
    /**
     * @var array<Closure(CommandJobsEvent): void>
     */
    private array $eventHandlers = [];

    public function __construct(
        private readonly CommandDefinitionRepository $commandDefinitionRepository,
        private readonly CommandJobRepository $commandJobRepository,
        private readonly CommandResultRepository $commandResultRepository,
        private readonly CommandExecutor $commandExecutor,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param Closure(CommandJobsEvent): void $callback
     */
    public function onEvent(Closure $callback): void
    {
        $this->eventHandlers[] = $callback;
    }

    /**
     * @return list<string>
     */
    public function commandDefinitionIds(): array
    {
        return $this->commandDefinitionRepository->getAll()->map(fn (CommandDefinition $commandDefinition) => $commandDefinition->id->value);
    }

    public function runPendingJobs(bool $stopOnError): CommandResults
    {
        $appliedCommands = $this->commandResultRepository->getAll()->withoutFailed()->getCommandJobIds();
        $pendingCommands = $this->commandJobRepository->getAll()->filter(fn (CommandJob $commandJob) => !$appliedCommands->contain($commandJob->id));
        $this->dispatch(new RunStarted($pendingCommands));
        $commandResults = [];
        foreach ($pendingCommands as $commandJob) {
            $commandResult = $this->executeCommand($commandJob);
            $commandResults[] = $commandResult;
            if ($stopOnError && !$commandResult->success) {
                break;
            }
        }
        $result = CommandResults::fromArray($commandResults);
        $this->dispatch(new RunEnded($result));
        return $result;
    }

    public function addCommandDefinition(CommandDefinitionId $commandDefinitionId, string $description, CommandWithArguments $commandWithArguments): CommandDefinition
    {
        $existingCommandDefinition = $this->commandDefinitionRepository->findById($commandDefinitionId);
        if ($existingCommandDefinition !== null) {
            throw new InvalidArgumentException(sprintf('Command definition "%s" already exists.', $commandDefinitionId->value), 1758629259);
        }
        $commandDefinition = new CommandDefinition($commandDefinitionId, $description, $commandWithArguments);
        $this->commandDefinitionRepository->add($commandDefinition);
        return $commandDefinition;
    }

    public function addCommandJob(CommandDefinitionId $commandDefinitionId): CommandJob
    {
        $commandDefinition = $this->commandDefinitionRepository->findById($commandDefinitionId);
        if ($commandDefinition === null) {
            throw new InvalidArgumentException(sprintf('Command definition "%s" does not exist.', $commandDefinitionId->value), 1758627579);
        }
        $commandJob = new CommandJob(CommandJobId::fromDateTime($this->clock->now()), $commandDefinitionId);
        $this->commandJobRepository->add($commandJob);
        return $commandJob;
    }

    private function executeCommand(CommandJob $commandJob): CommandResult
    {
        $commandDefinition = $this->commandDefinitionRepository->findById($commandJob->commandDefinitionId);
        if ($commandDefinition === null) {
            throw new RuntimeException(sprintf('Failed to find command definition with id "%s"', $commandJob->commandDefinitionId->value), 1758624333);
        }
        $executionTime = $this->clock->now();
        try {
            $output = trim($this->commandExecutor->run($commandDefinition->cmd));
            $commandResult = new CommandResult(
                commandJobId: $commandJob->id,
                commandDefinitionId: $commandDefinition->id,
                executionTime: $executionTime,
                executionDurationInMilliseconds: (int)(round(($this->clock->now()->format('U.u') - $executionTime->format('U.u')) * 1000)),
                success: true,
                output: $output,
            );
        } catch (Throwable $e) {
            $commandResult = new CommandResult(
                commandJobId: $commandJob->id,
                commandDefinitionId: $commandDefinition->id,
                executionTime: $executionTime,
                executionDurationInMilliseconds: (int)(round(($this->clock->now()->format('U.u') - $executionTime->format('U.u')) * 1000)),
                success: false,
                output: $e->getMessage(),
            );
        }
        $this->commandResultRepository->add($commandResult);
        $this->dispatch(new CommandExecuted($commandResult, $commandDefinition));
        return $commandResult;
    }

    private function dispatch(CommandJobsEvent $commandJobsEvent): void
    {
        foreach ($this->eventHandlers as $eventHandler) {
            $eventHandler($commandJobsEvent);
        }
    }
}
