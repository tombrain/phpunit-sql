<?php
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
