<?php
namespace Cz\PHPUnit\SQL;

use PHPUnit\Framework\Constraint\IsEqual,
    PHPUnit\Framework\ExpectationFailedException,
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
class EqualsSQLQueriesConstraint extends IsEqual
{
    /**
     * @var  array
     */
    private $queries;

    /**
     * @param  mixed     $value         string[] or string
     * @param  float     $delta
     * @param  integer   $maxDepth
     * @param  boolean   $canonicalize
     * @param  boolean   $ignoreCase
     */
    public function __construct($value, $delta = 0.0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        $this->queries = $this->toArray($value);
        $parsed = $this->parseQueries($this->queries);
        parent::__construct($parsed, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * @param   mixed    $other         string[] or string
     * @param   string   $description
     * @param   boolean  $returnResult
     * @return  mixed
     */
    public function evaluate($other, $description = '', $returnResult = FALSE)
    {
        $parsed = $this->parseQueries($this->toArray($other));
        try {
            return parent::evaluate($parsed, $description, $returnResult);
        }
        catch (ExpectationFailedException $e) {
            $f = new ComparisonFailure(
                $this->value,
                $parsed,
                $this->export($this->value),
                $this->export($parsed),
                FALSE,
                'Failed asserting that two SQL query sequences are equal (whitespace ignored).'
            );
            throw new ExpectationFailedException(
                trim($description."\n".$f->getMessage()),
                $f,
                $e
            );
        }
    }

    /**
     * @param   array  $queries
     * @return  string
     */
    private function export($queries)
    {
        $temp = [];
        foreach ($queries as $query) {
            $temp[] = implode(' ', $query);
        }
        return $this->exporter->export($temp);
    }

    /**
     * @param   array  $queries
     * @return  array
     */
    protected function parseQueries(array $queries)
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
    protected function toArray($value)
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
    private function tokenizeSQL($sql)
    {
        $token = '\\(|\\)|[\']|"|\140|<>|<=|>=|:=|[*\/<>,+=-]';
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

    private function tokSingleQuoteString($string)
    {
        // Matches a single-quoted string in $string
        // $string starts with a single quote
        preg_match('/^(\'.*?\').*$/s', $string, $matches);
        return $matches[1];
    }

    private function tokBackQuoteString($string)
    {
        // Matches a back-quoted string in $string
        // $string starts with a back quote
        preg_match('/^([\140].*?[\140]).*$/s', $string, $matches);
        return $matches[1];
    }

    private function tokDoubleQuoteString($string)
    {
        // Matches a back-quoted string in $string
        // $string starts with a back quote
        preg_match('/^(".*?").*$/s', $string, $matches);
        return $matches[1];
    }
}
