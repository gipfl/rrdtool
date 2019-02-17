<?php

namespace gipfl\Tests\RrdTool;

use gipfl\RrdTool\RraSet;
use PHPUnit\Framework\TestCase;

class RraSetTest extends TestCase
{
    public function testParseSimpleRraSet()
    {
        $str =
            // 2880 entries with 1 minute step = 48 hours
            'RRA:AVERAGE:0.5:1:2880'
            // 2880 entries with 5 minute step = 10 days
            . ' RRA:AVERAGE:0.5:5:2880'
            // 4320 entries with 30 minute step = 90 days
            . ' RRA:AVERAGE:0.5:30:4320'
            // 5840 entries with 360 minute step = 4 years
            . ' RRA:AVERAGE:0.5:360:5840';

         $rra = RraSet::fromString($str);
        $this->assertEquals($str, $rra->toString());
    }
}
