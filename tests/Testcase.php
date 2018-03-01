<?php
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
    public function createExpectationFailedException()
    {
        return new ExpectationFailedException('');
    }

    /**
     * @param  mixed  $expected
     */
    public function expectExceptionFromArgument($expected)
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }
}
