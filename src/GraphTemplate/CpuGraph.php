<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class CpuGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $parts = [
            'iowait'     => 'F96266', // iowait
            'softirq'    => 'F962F5', // softirq
            'irq'        => '8362F9', // irq
            'nice'       => 'D48823', // nice
            'steal'      => '000000',
            'guest'      => '333333',
            'guest_nice' => 'aaaaaa',
            'system'     => 'F9AF62', // sys
            'user'       => 'F9E962', // user
            'idle'       => '44bb77', // idle
        ];
        $defs = [];

        $first = true;
        foreach ($parts as $ds => $color) {
            $def = $graph->def($filename, $ds, 'AVERAGE');
            $defs[$ds] = $def;
            $graph->area($def, $color, ! $first);
            $first = false;
        }

        // 100 - sum(all values), 0 if negative:
        $cdef = '100,' . implode(',', array_values($defs)) . str_repeat(',+', count($defs) - 1)
            . ',-,0,MAX';
        $cdef = $graph->cdef($cdef);
        $graph->area($cdef, '00000033', true);

        $graph->setLowerLimit(0)->setUpperLimit(100)->setRigid();
    }
}
