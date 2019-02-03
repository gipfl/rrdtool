<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class CpuGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $parts = [
            'user'       => 'F9E962', // user
            'system'     => 'F9AF62', // sys
            'iowait'     => 'F96266', // iowait
            'softirq'    => 'F962F5', // softirq
            'irq'        => '8362F9', // irq
            'nice'       => 'D48823', // nice
            'idle'       => '44bb77', // idle
            'steal'      => '000000',
            'guest'      => '333333',
            'guest_nice' => 'aaaaaa',

        ];

        foreach ($parts as $ds => $color) {
            $def = $graph->def($filename, $ds, 'AVERAGE');
            $graph->area($def, $color, $ds !== 1);
        }
    }
}
