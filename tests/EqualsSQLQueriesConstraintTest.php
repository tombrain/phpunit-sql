<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use Cz\PHPUnit\SQL\Testcase;

/**
 * EqualsSQLQueriesConstraintTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class EqualsSQLQueriesConstraintTest extends Testcase
{
    /**
     * @dataProvider  provideEvaluate
     */
    public function testEvaluate($value, $other, bool $returnResult, $expected): void
    {
        $object = $this->createObject($value);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->evaluate($other, '', $returnResult);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test cases using `$returnResult=FALSE`
     */
    public static function provideEvaluateDefault(): array
    {
        return [
            'Pass (whitespace differences ignored)' => [
                "SELECT * FROM `t1` WHERE `a` = 1 AND `b` != 2",
                "SELECT *\nFROM `t1`\nWHERE `a` = 1\n\tAND `b` != 2",
                TRUE,
            ],
            'Pass (terminal semicolon ignored)' => [
                "SELECT * FROM `t1`",
                "SELECT * FROM `t1`;",
                TRUE,
            ],
            'Fail (quote identifier)' => [
                "SELECT * FROM `t1`",
                "SELECT * FROM t1",
                static::createExpectationFailedException(),
            ],
            'Fail (operator)' => [
                "SELECT * FROM `t1` WHERE `d` > NOW()",
                "SELECT * FROM `t1` WHERE `d` < NOW()",
                static::createExpectationFailedException(),
            ],
            'Pass, multiple queries' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                TRUE,
            ],
            'Fail, multiple queries' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["DELETE * FROM `t1`", "SELECT * FROM `t2`"],
                static::createExpectationFailedException(),
            ],
            'Fail, two queries in one string' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t1`; SELECT * FROM `t2`"],
                static::createExpectationFailedException(),
            ],
            'Fail, reverse order' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t2`", "SELECT * FROM `t1`"],
                static::createExpectationFailedException(),
            ],
            'Fail, one missing' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`", "SELECT * FROM `t3`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t3`"],
                static::createExpectationFailedException(),
            ],
            'Fail, one extra' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`", "SELECT * FROM `t3`"],
                static::createExpectationFailedException(),
            ],
            'Pass, mixed types (array, string)' => [
                ["SELECT * FROM `t1`"],
                "SELECT * FROM `t1`",
                TRUE,
            ],
            'Pass, mixed types (string, array)' => [
                "SELECT * FROM `t1`",
                ["SELECT * FROM `t1`"],
                TRUE,
            ],
        ];
    }

    /**
     * Test cases using `$returnResult=TRUE`
     */
    public static function provideEvaluateReturnResult(): array
    {
        return [
            'Return TRUE value' => [
                "SELECT * FROM `t1`",
                "SELECT * FROM `t1`",
                TRUE,
            ],
            'Return FALSE value' => [
                "SELECT * FROM `t1`",
                "DELETE * FROM `t1`",
                FALSE,
            ],
        ];
    }

    public static function provideEvaluate(): array
    {
        return array_merge(
            static::mapReturnResult(static::provideEvaluateDefault(), FALSE),
            static::mapReturnResult(static::provideEvaluateReturnResult(), TRUE)
        );
    }

    /**
     * @param   array    $cases
     * @param   boolean  $returnValue
     * @return  array
     */
    private static function mapReturnResult(array $cases, $returnValue): array
    {
        return array_combine(
            array_keys($cases),
            array_map(
                function ($arguments) use ($returnValue) {
                    list ($value, $other, $expected) = $arguments;
                    return [$value, $other, $returnValue, $expected];
                },
                $cases
            )
        );
    }

    /**
     * @param   mixed  $value
     * @return  EqualsSQLQueriesConstraint
     */
    private function createObject($value)
    {
        return new EqualsSQLQueriesConstraint($value);
    }
}
