<?php

namespace gipfl\Tests\RrdTool;

use gipfl\RrdTool\Rra;
use PHPUnit\Framework\TestCase;

class RraTest extends TestCase
{
    public function testParseSimpleRraString()
    {
        $str = 'RRA:MIN:0.5:21600:5840';
        $rra = Rra::fromString($str);
        $this->assertEquals($str, $rra->toString());
    }
}
