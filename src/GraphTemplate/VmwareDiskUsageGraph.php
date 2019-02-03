<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\RrdGraph;

class VmwareDiskUsageGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $capacity = $graph->def($filename, 'capacity', 'MAX');
        $free = $graph->def($filename, 'free_space', 'MIN');
        $used = $graph->cdef("$capacity,$free,-", 'used');

        $usedNormal = $graph->cdef("$used,$capacity,0.7,*,LT,$used,0,IF", 'used_warn');
        $usedWarn = $graph->cdef("$used,$capacity,0.7,*,GT,$used,0,IF", 'used_warn');
        $usedCrit = $graph->cdef("$used,$capacity,0.8,*,GT,$used,0,IF", 'used_warn');
        // $warn = $graph->cdef("$capacity,$free,-", 'used');
        $max = $graph->vdef("$capacity,MAXIMUM");
//         $graph->area($max, '44bb77', true);
        $graph->area($used, '#FFFFFF');
        $graph->area($free, '#44bb77', true);
        $graph->add([
            new Area($usedNormal, '#1A95D6'),
            new Area($usedWarn, '#F9E962'),
            new Area($usedCrit, '#FF6D85'),
        ]);
        $graph->printDef($max, '%5.4lf');
    }
}
