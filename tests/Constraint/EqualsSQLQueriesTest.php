<?php
namespace Cz\PHPUnit\SQL\Constraint;

use Cz\PHPUnit\SQL\Testcase;

/**
 * EqualsSQLQueriesTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class EqualsSQLQueriesTest extends Testcase
{
    /**
     * @dataProvider  provideEvaluate
     */
    public function testEvaluate($value, $other, $returnResult, $expected)
    {
        $object = $this->createObject($value);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->evaluate($other, '', $returnResult);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test cases using `$returnResult=FALSE`
     */
    public function provideEvaluateDefault()
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
                $this->createExpectationFailedException(),
            ],
            'Fail (operator)' => [
                "SELECT * FROM `t1` WHERE `d` > NOW()",
                "SELECT * FROM `t1` WHERE `d` < NOW()",
                $this->createExpectationFailedException(),
            ],
            'Pass, multiple queries' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                TRUE,
            ],
            'Fail, multiple queries' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["DELETE * FROM `t1`", "SELECT * FROM `t2`"],
                $this->createExpectationFailedException(),
            ],
            'Fail, reverse order' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t2`", "SELECT * FROM `t1`"],
                $this->createExpectationFailedException(),
            ],
            'Fail, one missing' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`", "SELECT * FROM `t3`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t3`"],
                $this->createExpectationFailedException(),
            ],
            'Fail, one extra' => [
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`"],
                ["SELECT * FROM `t1`", "SELECT * FROM `t2`", "SELECT * FROM `t3`"],
                $this->createExpectationFailedException(),
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
    public function provideEvaluateReturnResult()
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

    public function provideEvaluate()
    {
        return array_merge(
            $this->mapReturnResult($this->provideEvaluateDefault(), FALSE),
            $this->mapReturnResult($this->provideEvaluateReturnResult(), TRUE)
        );
    }

    /**
     * @param   array    $cases
     * @param   boolean  $returnValue
     * @return  array
     */
    private function mapReturnResult(array $cases, $returnValue)
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
     * @return  EqualsSQLQueries
     */
    private function createObject($value)
    {
        return new EqualsSQLQueries($value);
    }
}
