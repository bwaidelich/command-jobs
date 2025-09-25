<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandDefinition;

use Webmozart\Assert\Assert;

final readonly class CommandWithArguments
{

    /**
     * @param list<string> $commandWithArguments
     */
    private function __construct(
        private array $commandWithArguments,
    ) {
        Assert::notEmpty($commandWithArguments, 'Command must not be empty');
    }

    /**
     * @param list<string> $cmd
     */
    public static function fromParts(array $cmd): self
    {
        return new self($cmd);
    }

    /**
     * Converts a string like 'some-command some "composite argument"' into ['some-command', 'some', 'composite argument']
     */
    public static function fromString(string $cmd): self
    {
        $cmd = trim($cmd);
        if ($cmd === '') {
            return new self([]);
        }

        $parts = [];
        $currentPart = '';
        $inQuotes = false;
        $length = strlen($cmd);

        for ($i = 0; $i < $length; $i++) {
            $char = $cmd[$i];

            if ($char === '"' && ($i === 0 || $cmd[$i - 1] !== '\\')) {
                $inQuotes = !$inQuotes;
            } elseif ($char === ' ' && !$inQuotes) {
                if ($currentPart !== '') {
                    $parts[] = $currentPart;
                    $currentPart = '';
                }
            } elseif ($char === '\\' && $i + 1 < $length && $cmd[$i + 1] === '"') {
                $currentPart .= '"';
                $i++;
            } else {
                $currentPart .= $char;
            }
        }

        if ($currentPart !== '') {
            $parts[] = $currentPart;
        }

        return new self($parts);
    }

    public function toString(): string
    {
        return implode(' ', $this->commandWithArguments);
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->commandWithArguments;
    }
}
