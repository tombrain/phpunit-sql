<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\ExpectationFailedException,
    SebastianBergmann\Comparator\Factory as ComparatorFactory,
    SebastianBergmann\Comparator\ComparisonFailure;

/**
 * EqualsSQLQueriesConstraint
 * 
 * Compares series of SQL queries (actual to expected) after parsing them to arrays
 * using the tokenize function in order to ignore whitespace differences.
 * 
 * @author   czukowski
 * @license  MIT License
 */
class EqualsSQLQueriesConstraint extends Constraint
{
    /**
     * @var  array
     */
    private $queries;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param  string[]|string  $value
     */
    public function __construct($value)
    {
        $this->queries = $this->toArray($value);
        $this->value = $this->parseQueries($this->queries);
    }

    /**
     * @param   mixed    $other         string[] or string
     * @param   string   $description
     * @param   boolean  $returnResult
     * @return  mixed
     */
    public function evaluate($other, string $description = '', bool $returnResult = FALSE): ?bool
    {
        $otherParsed = $this->parseQueries($this->toArray($other));
        $comparatorFactory = ComparatorFactory::getInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $this->value,
                $otherParsed
            );

            $comparator->assertEquals($this->value, $otherParsed);
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return FALSE;
            }

            $message = 'Failed asserting that two SQL query sequences are equal (whitespace ignored).';
            throw new ExpectationFailedException(
                trim($message."\n".$f->getMessage()),
                new ComparisonFailure(
                    $this->value,
                    $otherParsed,
                    $this->export($this->value),
                    $this->export($otherParsed),
                    FALSE,
                    $message
                ),
                $f
            );
        }

        return TRUE;
    }

    /**
     * @param   array  $queries
     * @return  string
     */
    private function export(array $queries): string
    {
        $temp = [];
        foreach ($queries as $query) {
            $temp[] = implode(' ', $query);
        }
        return $this->exporter()->export($temp);
    }

    /**
     * @param   array  $queries
     * @return  array
     */
    protected function parseQueries(array $queries): array
    {
        $tokenized = [];
        foreach ($queries as $query) {
            $tokenized[] = $this->tokenizeSQL($query);
        }
        return $tokenized;
    }

    /**
     * @param   mixed  $value
     * @return  array
     */
    protected function toArray($value): array
    {
        if ( ! is_array($value)) {
            $value = [$value];
        }
        return $value;
    }

    /**
     * A naive SQL string tokenizer.
     * 
     * Note: the function is believed to originate from the URL below, which is now defunct.
     * @link  http://riceball.com/d/node/337
     * 
     * @param   string  $sql
     * @return  array
     */
    private function tokenizeSQL(string $sql): array
    {
        $token = '\\(|\\)|\[|\]|[\']|"|\140|<>|<=|>=|:=|[*\/<>,+=-]';
        $terminal = $token.'|;| |\\n';
        $result = [];
        $string = $sql;
        $string = ltrim($string);
        $string = rtrim($string, ';').';'; // always ends with a terminal
        $string = preg_replace("/[\n\r]/s", ' ', $string);
        while (
            preg_match("/^($token)($terminal)/s", $string, $matches) ||
            preg_match("/^({$token})./s", $string, $matches) ||
            preg_match("/^(@?[a-zA-Z0-9_.]+?)($terminal)/s", $string, $matches)
        ) {
            $t = $matches[1];
            if ($t == '\'') {
                // it's a string
                $t = $this->tokSingleQuoteString($string);
                array_push($result, $t);
            }
            elseif ($t == "\140") {
                // it's a backtick string (a name)
                $t = $this->tokBackQuoteString($string);
                array_push($result, $t);
            }
            elseif ($t == '"') {
                // it's a double quoted string (a name in normal sql)
                $t = $this->tokDoubleQuoteString($string);
                array_push($result, $t);
            }
            else {
                array_push($result, $t);
            }
            $string = substr($string, strlen($t));
            $string = ltrim($string);
        }
        return $result;
    }

    private function tokSingleQuoteString(string $string): string
    {
        // Matches a single-quoted string in $string
        // $string starts with a single quote
        preg_match('/^(\'.*?\').*$/s', $string, $matches);
        return $matches[1];
    }

    private function tokBackQuoteString(string $string): string
    {
        // Matches a back-quoted string in $string
        // $string starts with a back quote
        preg_match('/^([\140].*?[\140]).*$/s', $string, $matches);
        return $matches[1];
    }

    private function tokDoubleQuoteString(string $string): string
    {
        // Matches a back-quoted string in $string
        // $string starts with a back quote
        preg_match('/^(".*?").*$/s', $string, $matches);
        return $matches[1];
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'is equal to %s',
            $this->exporter()->export($this->value)
        );
    }
}
