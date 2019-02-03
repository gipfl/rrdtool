<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class CpuMetricsGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $parts = [
            '1' => 'F9E962', // user
            '3' => 'F9AF62', // sys
            '5' => 'F96266', // iowait
            '4' => 'F962F5', // softirq
            '6' => '8362F9', // irq
            '2' => 'D48823', // nice
            '7' => '44bb77', // idle
        ];

        foreach ($parts as $ds => $color) {
            $def = $graph->def($filename, $ds, 'AVERAGE');
            $graph->area($def, $color, $ds !== 1);
        }
    }
}
