<?php

declare(strict_types=1);

namespace wwwision\commandJobs\tests\commandDefinition;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use wwwision\commandJobs\commandDefinition\CommandWithArguments;

#[CoversClass(CommandWithArguments::class)]
final class CommandWithArgumentsTest extends TestCase
{

    public static function fromString_with_invalid_input_dataProvider(): iterable
    {
        yield 'empty string' => ['input' => ''];
    }

    #[DataProvider('fromString_with_invalid_input_dataProvider')]
    public function test_fromString_with_invalid_input(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        CommandWithArguments::fromString($input);
    }

    public static function fromString_with_valid_input_dataProvider(): iterable
    {
        yield 'single word' => ['input' => 'some-command', 'expectedResult' => ['some-command']];
        yield 'multiple words' => ['input' => 'some-command with-some arguments', 'expectedResult' => ['some-command', 'with-some', 'arguments']];
        yield 'double quoted argument' => ['input' => 'cmd "some-arg"', 'expectedResult' => ['cmd', 'some-arg']];
    }

    #[DataProvider('fromString_with_valid_input_dataProvider')]
    public function test_fromString_with_valid_input(string $input, array $expectedResult): void
    {
        self::assertSame($expectedResult, CommandWithArguments::fromString($input)->toArray());
    }
}