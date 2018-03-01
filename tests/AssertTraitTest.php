<?php
namespace Cz\PHPUnit\SQL;

use LogicException,
    PHPUnit\Framework\Assert,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\Exception,
    ReflectionMethod;

/**
 * AssertTraitTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AssertTraitTest extends Testcase
{
    /**
     * @dataProvider  provideAssertEqualsSQLQueries
     */
    public function testAssertEqualsSQLQueriesWithMockObject($arguments, $expected)
    {
        $this->runTestAssertEqualsSQLQueries($this->createMockObject(), $arguments, $expected);
    }

    /**
     * @dataProvider  provideAssertEqualsSQLQueries
     */
    public function testAssertEqualsSQLQueriesWithStubObject($arguments, $expected)
    {
        $this->runTestAssertEqualsSQLQueries($this->createStubObject(), $arguments, $expected);
    }

    private function runTestAssertEqualsSQLQueries($object, $arguments, $expected)
    {
        $this->expectExceptionFromArgument($expected);
        $actual = $object->assertEqualsSQLQueries(...$arguments);
        $this->assertSame($expected, $actual);
    }

    public function provideAssertEqualsSQLQueries()
    {
        return [
            [
                [NULL, 3.14],
                new Exception,
            ],
            [
                [TRUE, FALSE],
                new Exception,
            ],
            [
                ['', ''],
                NULL,
            ],
            [
                ["SELECT * FROM `t1`", "SELECT * FROM `t1`"],
                NULL,
            ],
            [
                ["SELECT * FROM `t1`", "DELETE * FROM `t1`"],
                $this->createExpectationFailedException(),
            ],
        ];
    }

    /**
     * @dataProvider  provideAssertExecutedSQLQueries
     */
    public function testAssertExecutedSQLQueriesWithMockObject($arguments, $executed, $expected)
    {
        $object = $this->createMockObject($this->createDbDriverMock($executed));
        $this->runTestAssertExecutedSQLQueries($object, $arguments, $expected);
    }

    /**
     * @dataProvider  provideAssertExecutedSQLQueries
     */
    public function testAssertExecutedSQLQueriesWithStubObject($arguments, $executed, $expected)
    {
        $object = $this->createStubObject($this->createDbDriverMock($executed));
        $this->runTestAssertExecutedSQLQueries($object, $arguments, $expected);
    }

    private function runTestAssertExecutedSQLQueries($object, $arguments, $expected)
    {
        $this->expectExceptionFromArgument($expected);
        $actual = $object->assertExecutedSQLQueries(...$arguments);
        $this->assertSame($expected, $actual);
    }

    public function provideAssertExecutedSQLQueries()
    {
        return [
            [
                ['SELECT * FROM `t1`'],
                NULL,
                new LogicException,
            ],
            [
                ['SELECT * FROM `t1`'],
                ['SELECT * FROM `t1`'],
                NULL,
            ],
            [
                [
                    ['SELECT * FROM `t1`'],
                ],
                ['SELECT * FROM `t1`'],
                NULL,
            ],
            [
                [
                    ['SELECT * FROM `t1`', 'SELECT * FROM `t2`'],
                ],
                ['SELECT * FROM `t1`', 'SELECT * FROM `t2`'],
                NULL,
            ],
            [
                [
                    ['SELECT * FROM `t1`'],
                ],
                [],
                $this->createExpectationFailedException(),
            ],
            [
                [
                    ['SELECT * FROM `t1`', 'SELECT * FROM `t2`'],
                ],
                ['SELECT * FROM `t1`', 'DELETE * FROM `t2`'],
                $this->createExpectationFailedException(),
            ],
        ];
    }

    /**
     * @dataProvider  provideLoadSQLQueries
     */
    public function testLoadSQLQueries($filename, $expected)
    {
        $object = $this->createStubObject();
        $loadSQLQueries = new ReflectionMethod($object, 'loadSQLQueries');
        $loadSQLQueries->setAccessible(TRUE);
        $actual = $loadSQLQueries->invoke($object, $filename);
        $this->assertEquals($expected, $actual);
    }

    public function provideLoadSQLQueries()
    {
        return [
            [
                'Test.sql',
                ["SELECT * FROM `t1`"],
            ],
        ];
    }

    private function createDbDriverMock($executed)
    {
        // `$executed=NULL` is a special case for when a database driver is not set/implemented.
        if ($executed !== NULL) {
            $db = $this->getMockForAbstractClass(DatabaseDriverInterface::class);
            $db->expects($this->any())
                ->method('getExecutedQueries')
                ->willReturn($executed);
            return $db;
        }
    }

    private function createMockObject($dbDriverMock = NULL)
    {
        $methods = $dbDriverMock === NULL ? [] : ['getDatabaseDriver'];
        $object = $this->getMockForTrait(AssertTrait::class, [], '', TRUE, TRUE, TRUE, $methods);
        $object->expects($this->any())
            ->method('assertThat')
            ->willReturnCallback(function ($value, Constraint $constraint, $message = '') {
                return Assert::assertThat($value, $constraint, $message);
            });
        if ($dbDriverMock !== NULL) {
            $object->expects($this->any())
                ->method('getDatabaseDriver')
                ->willReturn($dbDriverMock);
        }
        return $object;
    }

    private function createStubObject($dbDriverMock = NULL)
    {
        return new AssertTraitObjectExtendsAssert($dbDriverMock);
    }
}
