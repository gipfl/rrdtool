<?php

namespace gipfl\Tests\RrdTool;

use gipfl\RrdTool\Ds;
use PHPUnit\Framework\TestCase;

class DsTest extends TestCase
{
    public function testParsesSimpleDsDefinitions()
    {
        $ds = new Ds('temperature', 'GAUGE', 8640);
        $this->assertEquals('DS:temperature:GAUGE:8640:U:U', $ds->toString());
        $ds = new Ds('temperature', 'GAUGE', 8640, 0);
        $this->assertEquals('DS:temperature:GAUGE:8640:0:U', $ds->toString());
        $ds = new Ds('temperature', 'GAUGE', 8640, null, 10000);
        $this->assertEquals('DS:temperature:GAUGE:8640:U:10000', $ds->toString());
        $ds = new Ds('temperature', 'GAUGE', 8640, -371, 10000);
        $this->assertEquals('DS:temperature:GAUGE:8640:-371:10000', $ds->toString());
    }
}
