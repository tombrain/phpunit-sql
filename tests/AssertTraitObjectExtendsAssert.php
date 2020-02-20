<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use LogicException,
    PHPUnit\Framework\Assert;

/**
 * AssertTraitObjectExtendsAssert
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AssertTraitObjectExtendsAssert extends Assert
{
    use AssertTrait;

    private $dbDriverMock;

    public function __construct(?DatabaseDriverInterface $dbDriverMock)
    {
        $this->dbDriverMock = $dbDriverMock;
    }

    protected function getDatabaseDriver(): DatabaseDriverInterface
    {
        if ($this->dbDriverMock === NULL) {
            throw new LogicException('DB driver mock not set');
        }
        return $this->dbDriverMock;
    }
}
