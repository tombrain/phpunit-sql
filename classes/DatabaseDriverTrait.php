<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

/**
 * DatabaseDriverTrait
 * 
 * Implements `DatabaseDriverInterface` method for easy inclusion into custom implementations.
 * 
 * @author   czukowski
 * @license  MIT License
 */
trait DatabaseDriverTrait
{
    /**
     * @var  array
     */
    private $executedQueries = [];

    /**
     * @param  string  $sql
     */
    public function addExecutedQuery($sql): void
    {
        $this->executedQueries[] = $sql;
    }

    /**
     * @return  array
     */
    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }
}
