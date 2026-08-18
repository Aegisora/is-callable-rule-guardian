<?php

namespace Aegisora\RuleGuardians\IsCallableRule\Tests\Unit;

use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\IsCallableRule\IsCallableRuleGuardian;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

class IsCallableRuleGuardianTest extends TestCase
{
    private const RULE_CODE = 'is_callable_rule';

    private IsCallableRuleGuardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = new IsCallableRuleGuardian(
            new Guardian()
        );
    }

    /**
     * @dataProvider getCallableValuesProvidedData
     * @param mixed $value
     */
    public function testSuccessfullyCheck(
        $value
    ): void {
        $this->expectNotToPerformAssertions();

        $this->guardian->check($value);
    }

    public static function getCallableValuesProvidedData(): array
    {
        return [
            'value - closure' => [
                'value' => static fn (): string => 'ok',
            ],
            'value - anonymous class with invoke' => [
                'value' => new class {
                    public function __invoke(): string
                    {
                        return 'ok';
                    }
                },
            ],
            'value - global standard function name' => [
                'value' => 'trim',
            ],
            'value - static method array callable' => [
                'value' => [self::class, 'staticCallableMethod'],
            ],
            'value - object method callable' => [
                'value' => [new self(), 'instanceCallableMethod'],
            ],
        ];
    }

    /**
     * @dataProvider getNotCallableValuesProvidedData
     * @param mixed $value
     */
    public function testFailedCheckWithDefaultCustomException(
        $value
    ): void {
        $this->expectException(GuardianValidationException::class);

        try {
            $this->guardian->check($value);
        } catch (GuardianValidationException $exception) {
            self::assertSame(self::RULE_CODE, $exception->getRuleCode());

            throw $exception;
        }
    }

    public static function getNotCallableValuesProvidedData(): array
    {
        return [
            'value - zero integer' => [
                'value' => 0,
            ],
            'value - positive integer' => [
                'value' => 1,
            ],
            'value - negative integer' => [
                'value' => -1,
            ],
            'value - zero float' => [
                'value' => 0.0,
            ],
            'value - positive float' => [
                'value' => 0.01,
            ],
            'value - negative float' => [
                'value' => -0.01,
            ],
            'value - empty string' => [
                'value' => '',
            ],
            'value - not empty string' => [
                'value' => 'fooo',
            ],
            'value - empty array' => [
                'value' => [],
            ],
            'value - not empty array' => [
                'value' => [1,],
            ],
            'value - object' => [
                'value' => new stdClass(),
            ],
            'value - resource' => [
                'value' => tmpfile(),
            ],
            'value - invalid array callable' => [
                'value' => ['UnknownClass', 'method'],
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckProvidedData
     * @param mixed $value
     */
    public function testFailedCheck(
        $value,
        ?Throwable $customRuleValidationException,
        string $expectedExceptionClassName
    ): void {
        $this->expectException($expectedExceptionClassName);

        $this->guardian->check($value, $customRuleValidationException);
    }

    public static function getFailedCheckProvidedData(): array
    {
        return [
            'value - zero integer, custom exception - null' => [
                'value' => 0,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - zero integer, custom exception - not null' => [
                'value' => 0,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - positive integer, custom exception - null' => [
                'value' => 1,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - positive integer, custom exception - not null' => [
                'value' => 1,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - negative integer, custom exception - null' => [
                'value' => -1,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - negative integer, custom exception - not null' => [
                'value' => -1,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - zero float, custom exception - null' => [
                'value' => 0.0,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - zero float, custom exception - not null' => [
                'value' => 0.0,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - positive float, custom exception - null' => [
                'value' => 0.01,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - positive float, custom exception - not null' => [
                'value' => 0.01,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - negative float, custom exception - null' => [
                'value' => -0.01,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - negative float, custom exception - not null' => [
                'value' => -0.01,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - empty string, custom exception - null' => [
                'value' => '',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - empty string, custom exception - not null' => [
                'value' => '',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - not empty string, custom exception - null' => [
                'value' => 'fooo',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - not empty string, custom exception - not null' => [
                'value' => 'fooo',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - empty array, custom exception - null' => [
                'value' => [],
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - empty array, custom exception - not null' => [
                'value' => [],
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - not empty array, custom exception - null' => [
                'value' => [1,],
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - not empty array, custom exception - not null' => [
                'value' => [1,],
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - object, custom exception - null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - object, custom exception - not null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - resource, custom exception - null' => [
                'value' => tmpfile(),
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - resource, custom exception - not null' => [
                'value' => tmpfile(),
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - invalid array callable, custom exception - null' => [
                'value' => ['UnknownClass', 'method'],
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - invalid array callable, custom exception - not null' => [
                'value' => ['UnknownClass', 'method'],
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
        ];
    }

    public function testFailedCheckCauseGuardianThrowsGuardianExecutingRuleException(): void
    {
        $this->expectException(GuardianExecutingRuleException::class);

        $guardian = new IsCallableRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(GuardianExecutingRuleException::class)
        );

        $guardian->check(null);
    }

    public function testFailedCheckCauseGuardianThrowsNotExpectedException(): void
    {
        $this->expectException(Throwable::class);

        $guardian = new IsCallableRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(Throwable::class)
        );

        $guardian->check(null);
    }

    public static function staticCallableMethod(): string
    {
        return 'ok';
    }

    public function instanceCallableMethod(): string
    {
        return 'ok';
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianThrowsExceptionOnCheck(string $expectedExceptionClass): Guardian
    {
        $guardian = $this->getGuardianMock();

        $guardian
            ->expects(self::once())
            ->method('check')
            ->willThrowException($this->createMock($expectedExceptionClass));

        return $guardian;
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianMock(): Guardian
    {
        /** @var Guardian|MockObject $mock */
        $mock = $this->createMock(Guardian::class);

        return $mock;
    }
}
