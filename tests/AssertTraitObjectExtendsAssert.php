<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use LogicException,
    PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * AssertTraitObjectExtendsAssert
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AssertTraitObjectExtendsAssert
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
    
    public function assertThat($value, Constraint $constraint, string $message = ''): void
    {
        Assert::assertThat($value, $constraint, $message);
    }
}
