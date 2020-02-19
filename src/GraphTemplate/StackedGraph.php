<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Instruction\Area;
use gipfl\RrdTool\RrdGraph;

abstract class StackedGraph extends Template
{
    protected $parts = [];

    protected $disabledDsNames = [];

    protected $min = 0;

    protected $max;

    protected function getParts()
    {
        return $this->parts;
    }

    // Overridden
    protected function disableDs($dsName)
    {
    }

    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $parts = $this->getParts();
        $defs = [];

        foreach ($this->getArrayParam('disableDatasources') as $dsName) {
            // TODO: disable real names
            $dsName = \str_replace([' ', '/'], '', \strtolower($dsName));
            $this->disabledDsNames[$dsName] = $dsName;
            $this->disableDs($dsName);
            unset($parts[$dsName]);
        }

        $first = true;
        $shift = $this->getParam('shift');
        $min = $this->getParam('min', $this->min);
        $max = $this->getParam('max', $this->max);

        // This is to show what is missing to reach Max in a Stack
        $showMissingOnTop = $this->getParam('showMissingOnTop');

        foreach ($parts as $ds => $color) {
            $def = $graph->def($filename, $ds, 'AVERAGE');
            $defs[$ds] = $def;

            // Shift test
            if ($shift !== null && $first) {
                $cdef = $graph->cdef("$def,0,*,$shift,+", 'shift');
                $graph->add(new Area($cdef, '00000000'));
                $first = false;
            }
            // Shift end

            $area = new Area($def, $color);
            $area->setStack(! $first);
            $graph->add($area);
            $first = false;
        }

        if ($max !== null) {
            $graph->setUpperLimit(max($max + $shift, 0))->setRigid();

            if ($showMissingOnTop) {
                // 100 - sum(all values), 0 if negative:
                $cdef = "$max," . implode(',', array_values($defs))
                    . str_repeat(',+', count($defs) - 1)
                    . ',-,0,MAX';
                $cdef = $graph->cdef($cdef);
                // This colors the missing part:
                $graph->area($cdef, '00000033', true);
            }
        }
        if ($min !== null) {
            $graph->setLowerLimit(min($shift, 0))->setRigid();
        }
    }
}
