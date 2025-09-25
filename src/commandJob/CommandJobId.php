<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJob;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

/**
 * Timestamp in the format 'YmdHis'
 */
final readonly class CommandJobId
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromDateTime(DateTimeImmutable $dateTime): self
    {
        return new self($dateTime->format('YmdHis'));
    }

    public static function fromString(string $value): self
    {
        $dateTime = DateTimeImmutable::createFromFormat('YmdHis', $value);
        Assert::isInstanceOf($dateTime, DateTimeImmutable::class, sprintf('The string "%s" is not a valid timestamp in the format YmdHis', $value));
        Assert::same($dateTime->format('YmdHis'), $value);
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
