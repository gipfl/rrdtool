<?php

namespace gipfl\Tests\RrdTool;

use gipfl\RrdTool\AsyncHelper;
use gipfl\RrdTool\AsyncRrdtool;
use gipfl\RrdTool\RrdInfo;
use PHPUnit\Framework\TestCase;

class AsyncRrdtoolTest extends TestCase
{
    use AsyncHelper;

    /**
     * @throws \Exception
     */
    public function testInfoCommandGivesResult()
    {
        $tool = $this->asyncRrdTool();
        $filename = 'simple-temperature.rrd';
        $result = $this->waitForValue($tool->send("info $filename"));
        $pattern = '/filename = "([^"]+)"/';
        $matched = preg_match($pattern, $result, $match);
        $this->assertEquals(1, $matched, 'info output had no filename');
        $this->assertEquals($filename, $match[1]);
    }

    /**
     * @throws \Exception
     */
    public function testRraSetInfoCanBeReadFromFile()
    {
        $tool = $this->asyncRrdTool();
        $filename = 'simple-temperature.rrd';
        /** @var RrdInfo $info */
        $info = $this->waitForValue($tool->info($filename));
        $this->assertInstanceOf(RrdInfo::class, $info);
        $this->assertEquals($filename, $info->getFilename());
        $rraSet = 'RRA:AVERAGE:0.5:1:2880 RRA:AVERAGE:0.5:5:2880 RRA:AVERAGE:0.5:30:4320 RRA:AVERAGE:0.5:360:5840';
        $this->assertEquals(
            $rraSet,
            $info->getRraSet()->toString()
        );
    }

    protected function asyncRrdTool()
    {
        $basedir = __DIR__ . '/files';
        $binary = '/usr/bin/rrdtool';

        return new AsyncRrdtool($basedir, $binary);
    }
}
