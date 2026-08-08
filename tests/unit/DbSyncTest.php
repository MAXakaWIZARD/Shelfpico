<?php

use App\Service\DbSync;

class DbSyncTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider providerParseDbVersionFromDumpPath
     * @param string $dumpPath
     * @param string $expected
     */
    public function testParseDbVersionFromDumpPath($dumpPath, $expected)
    {
        $actual = DbSync::parseVersion($dumpPath);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function providerParseDbVersionFromDumpPath()
    {
        return [
            ['/Volumes/DATA/shelfpico/db/shelfpico_2016-08-26_15_04_35_Mac.sql', '2016-08-26_15_04_35_Mac'],
            ['L:\shelfpico\db\shelfpico_2016-08-27_04_01_28_Win.sql', '2016-08-27_04_01_28_Win'],
            ['L:\shelfpico\db\shelfpico_2016-08-27_04_01_28_Win_structure.sql', '2016-08-27_04_01_28_Win'],
        ];
    }
}
