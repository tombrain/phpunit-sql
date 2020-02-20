<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

/**
 * DatabaseDriverInterface
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface DatabaseDriverInterface
{
    /**
     * @return  array
     */
    public function getExecutedQueries(): array;
}
