<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use ReflectionClass,
    RuntimeException;

/**
 * FileLoader
 * 
 * @author   czukowski
 * @license  MIT License
 */
class FileLoader
{
    /**
     * Based on `Dibi\Connection::loadFromFile`.
     * 
     * @link       https://github.com/dg/dibi/blob/432d0a8f7cb0d7f69552c9df22483af1820528a6/src/Dibi/Helpers.php#L232-L278
     * @author     David Grudl
     * @copyright  2005 David Grudl
     * @license    New BSD License
     * 
     * @param   string  $path
     * @return  array
     */
    public static function loadSQLFile(string $path): array
    {
        $queries = [];
        $handle = @fopen($path, 'r');  // intentionally @
        if ( ! $handle) {
            throw new RuntimeException("Cannot open file '$path'.");
        }

        $delimiter = ';';
        $sql = '';
        while (($s = fgets($handle)) !== FALSE) {
            if (strtoupper(substr($s, 0, 10)) === 'DELIMITER ') {
                $delimiter = trim(substr($s, 10));
            }
            elseif (substr($ts = rtrim($s), -strlen($delimiter)) === $delimiter) {
                $sql .= substr($ts, 0, -strlen($delimiter));
                $queries[] = $sql;
                $sql = '';
            }
            else {
                $sql .= $s;
            }
        }

        if (trim($sql) !== '') {
            $queries[] = $sql;
        }

        fclose($handle);
        return $queries;
    }

    /**
     * @param   mixed   $object
     * @param   string  $filename
     * @return  string
     */
    public static function getFilePathFromObjectSubdirectory($object, string $filename): string
    {
        $class = new ReflectionClass($object);
        $path = $class->getFileName();
        return dirname($path).'/'.basename($path, '.php').'/'.$filename;
    }
}
