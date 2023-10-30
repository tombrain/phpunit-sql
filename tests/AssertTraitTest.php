<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use LogicException,
    PHPUnit\Framework\Assert,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\MockObject\MockObject,
    ReflectionMethod,
    Throwable;

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
    public function testAssertEqualsSQLQueriesWithMockObject(array $arguments, ?Throwable $expected): void
    {
        $this->runTestAssertEqualsSQLQueries($this->createMockObject(), $arguments, $expected);
    }

    /**
     * @dataProvider  provideAssertEqualsSQLQueries
     */
    public function testAssertEqualsSQLQueriesWithStubObject(array $arguments, ?Throwable $expected): void
    {
        $this->runTestAssertEqualsSQLQueries($this->createStubObject(), $arguments, $expected);
    }

    private function runTestAssertEqualsSQLQueries($object, array $arguments, ?Throwable $expected): void
    {
        $this->expectExceptionFromArgument($expected);
        $actual = $object->assertEqualsSQLQueries(...$arguments);
        $this->assertSame($expected, $actual);
    }

    public static function provideAssertEqualsSQLQueries(): array
    {
        return [
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
                static::createExpectationFailedException(),
            ],
        ];
    }

    /**
     * @dataProvider  provideAssertExecutedSQLQueries
     */
    public function testAssertExecutedSQLQueriesWithMockObject(array $arguments, ?array $executed, ?Throwable $expected): void
    {
        $object = $this->createMockObject($this->createDbDriverMock($executed));
        $this->runTestAssertExecutedSQLQueries($object, $arguments, $expected);
    }

    /**
     * @dataProvider  provideAssertExecutedSQLQueries
     */
    public function testAssertExecutedSQLQueriesWithStubObject(array $arguments, ?array $executed, ?Throwable $expected): void
    {
        $object = $this->createStubObject($this->createDbDriverMock($executed));
        $this->runTestAssertExecutedSQLQueries($object, $arguments, $expected);
    }

    private function runTestAssertExecutedSQLQueries($object, array $arguments, ?Throwable $expected): void
    {
        $this->expectExceptionFromArgument($expected);
        $actual = $object->assertExecutedSQLQueries(...$arguments);
        $this->assertSame($expected, $actual);
    }

    public static function provideAssertExecutedSQLQueries(): array
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
                static::createExpectationFailedException(),
            ],
            [
                [
                    ['SELECT * FROM `t1`', 'SELECT * FROM `t2`'],
                ],
                ['SELECT * FROM `t1`', 'DELETE * FROM `t2`'],
                static::createExpectationFailedException(),
            ],
            [
                [
                    [
                        ['SELECT * FROM `t1`'],
                    ],
                ],
                ['SELECT * FROM `t1`'],
                NULL,
            ],
            [
                [
                    [
                        ['SELECT * FROM `t1`', 'DELETE * FROM `t2`'],
                        ['SELECT COUNT(*) FROM `t2`'],
                    ],
                ],
                ['SELECT * FROM `t1`', 'DELETE * FROM `t2`', ['SELECT COUNT(*) FROM `t2`']],
                NULL,
            ],
        ];
    }

    /**
     * @dataProvider  provideLoadSQLQueries
     */
    public function testLoadSQLQueries(string $filename, array $expected): void
    {
        $object = $this->createStubObject();
        $loadSQLQueries = new ReflectionMethod($object, 'loadSQLQueries');
        $loadSQLQueries->setAccessible(TRUE);
        $actual = $loadSQLQueries->invoke($object, $filename);
        $this->assertEquals($expected, $actual);
    }

    public static function provideLoadSQLQueries(): array
    {
        return [
            [
                'Test.sql',
                ["SELECT * FROM `t1`"],
            ],
        ];
    }

    private function createDbDriverMock($executed): ?DatabaseDriverInterface
    {
        // `$executed=NULL` is a special case for when a database driver is not set/implemented.
        if ($executed !== NULL) {
            $db = $this->getMockForAbstractClass(DatabaseDriverInterface::class);
            $db->expects($this->any())
                ->method('getExecutedQueries')
                ->willReturn($executed);
            return $db;
        }
        return NULL;
    }

    private function createMockObject(?DatabaseDriverInterface $dbDriverMock = NULL): MockObject
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

    private function createStubObject(?DatabaseDriverInterface $dbDriverMock = NULL): AssertTraitObjectExtendsAssert
    {
        return new AssertTraitObjectExtendsAssert($dbDriverMock);
    }
}
