<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Line;
use gipfl\RrdTool\RrdGraph;

class VmwareDiskTotalLatencyGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        $readColor = new Color('#57985B');
        $writeColor = new Color('#0095BF');

        // $read = $graph->def($filename, 'totalReadLatency', 'AVERAGE');
        // $write = $graph->def($filename, 'totalWriteLatency', 'AVERAGE');
        $read = $graph->def($filename, 'readLatencyUS', 'AVERAGE');
        $read = $graph->cdef("$read,1000000,/");
        $write = $graph->def($filename, 'writeLatencyUS', 'AVERAGE');
        $write = $graph->cdef("$write,-1,/,1000000,/");

        $graph->add([
            new Line($read, $readColor),
            new Area($read, $readColor->setAlphaHex('66')),
            new Line($write, $writeColor),
            new Area($write, $writeColor->setAlphaHex('66')),
        ]);
        $graph->setLowerLimit(null);
    }
}
