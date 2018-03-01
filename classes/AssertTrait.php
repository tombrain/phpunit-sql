<?php
namespace Cz\PHPUnit\SQL;

use Cz\PHPUnit\SQL\Constraint\EqualsSQLQueries,
    LogicException,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Util\InvalidArgumentHelper;

/**
 * AssertTrait
 * 
 * @author   czukowski
 * @license  MIT License
 */
trait AssertTrait
{
    /**
     * Assert two SQL queries or two series of SQL queries are equal.
     * 
     * @param  mixed    $expected      string[] or string
     * @param  mixed    $actual        string[] or string
     * @param  string   $message
     * @param  float    $delta
     * @param  integer  $maxDepth
     * @param  boolean  $canonicalize
     * @param  boolean  $ignoreCase
     */
    public function assertEqualsSQLQueries(
        $expected,
        $actual,
        $message = '',
        $delta = 0.0,
        $maxDepth = 10,
        $canonicalize = FALSE,
        $ignoreCase = FALSE
    ) {
        foreach ([$expected, $actual] as $i => $argument) {
            if ( ! is_string($argument) && ! is_array($argument)) {
                throw InvalidArgumentHelper::factory(
                    $i + 1,
                    'string or array'
                );
            }
        }
        $constraint = new EqualsSQLQueries($expected, $delta, $maxDepth, $canonicalize, $ignoreCase);
        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts the executed SQL equal the expected values.
     * Works only if `getDatabaseDriver` method is implemented.
     * 
     * @param  mixed    $expected      string[] or string
     * @param  string   $message
     * @param  float    $delta
     * @param  integer  $maxDepth
     * @param  boolean  $canonicalize
     * @param  boolean  $ignoreCase
     */
    public function assertExecutedSQLQueries(
        $expected,
        $message = '',
        $delta = 0.0,
        $maxDepth = 10,
        $canonicalize = FALSE,
        $ignoreCase = FALSE
    ) {
        $actual = $this->getDatabaseDriver()
            ->getExecutedQueries();
        $this->assertEqualsSQLQueries($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * @return  DatabaseDriverInterface
     * @throws  LogicException
     */
    protected function getDatabaseDriver()
    {
        // Override this method to return a `DatabaseDriverInterface` instance.
        throw new LogicException('Missing implementation');
    }

    /**
     * @param  mixed       $value
     * @param  Constraint  $constraint
     * @param  string      $message
     */
    abstract public function assertThat($value, Constraint $constraint, $message = '');
}
