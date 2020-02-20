<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

/**
 * FileLoaderTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class FileLoaderTest extends Testcase
{
    /**
     * @dataProvider  provideLoadSQLFile
     */
    public function testLoadSQLFile(string $filename, array $expected): void
    {
        $object = new FileLoader;
        $path = $object->getFilePathFromObjectSubdirectory($this, $filename);
        $actual = $object->loadSQLFile($path);
        $this->assertEquals($expected, $actual);
    }

    public function provideLoadSQLFile(): array
    {
        return [
            'Query not starting with new line, no split!' => [
                'Test0.sql',
                ["SELECT * FROM `t1`; SELECT * FROM `t2`"],
            ],
            'Not terminated' => [
                'Test1.sql',
                ["SELECT * FROM `t1`"],
            ],
            'Terminated by semicolon' => [
                'Test2.sql',
                ["SELECT * FROM `t1`"],
            ],
            'Terminated by semicolon, new line' => [
                'Test3.sql',
                ["SELECT * FROM `t1`"],
            ],
            'Multiple queries' => [
                'Test4.sql',
                [
                    "SELECT * FROM `t1`",
                    "SELECT * FROM `t2`",
                ],
            ],
            'Quoted semicolons' => [
                'Test5.sql',
                [
                    "SELECT COUNT(*) AS `items;count` FROM `t1`",
                    "SELECT * FROM `t2` WHERE `a` LIKE '%;%'",
                ],
            ],
            'Change of delimiter' => [
                'Test6.sql',
                [
                    "SELECT * FROM `t1`",
                    "SELECT * FROM `t2`",
                    "SELECT * FROM `t3`",
                ],
            ],
            'Multiline query' => [
                'Test7.sql',
                [
                    implode("\n", [
                        "SELECT *",
                        "FROM `t1`",
                        "JOIN `t2` ON `t1`.`a` = `t2`.`b`",
                        "WHERE `t1`.`b` IS NULL",
                        "    AND `t2`.`b` IS NOT NULL",
                    ]),
                ],
            ],
        ];
    }
}
