<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\RrdGraph;

class CpuGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $parts = [
            'iowait'     => '#F96266',
            'softirq'    => '#F962F5',
            'irq'        => '#8362F9',
            'nice'       => '#D48823',
            'steal'      => '#000000',
            'guest'      => '#333333',
            'guest_nice' => '#aaaaaa',
            'system'     => '#F9AF62',
            'user'       => '#F9E962',
            'idle'       => '#44bb77',
        ];
        $defs = [];

        $first = true;
        foreach ($parts as $ds => $color) {
            $def = $graph->def($filename, $ds, 'AVERAGE');
            $defs[$ds] = $def;
            $area = new Area($def, $color);
            $area->setStack(! $first);
            $graph->add($area);
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
