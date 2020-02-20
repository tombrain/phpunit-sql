<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use Exception,
    PHPUnit\Framework\TestCase as FrameworkTestCase,
    PHPUnit\Framework\ExpectationFailedException;

/**
 * Testcase
 * 
 * @author   czukowski
 * @license  MIT License
 */
abstract class Testcase extends FrameworkTestCase
{
    /**
     * @return  ExpectationFailedException
     */
    public function createExpectationFailedException(): ExpectationFailedException
    {
        return new ExpectationFailedException('');
    }

    /**
     * @param  mixed  $expected
     */
    public function expectExceptionFromArgument($expected): void
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }
}
