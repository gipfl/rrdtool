<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class CpuGraph extends StackedGraph
{
    protected $parts = [
        'iowait'     => '#F96266aa',
        'softirq'    => '#F962F5aa',
        'irq'        => '#8362F9aa',
        'nice'       => '#D48823aa',
        'steal'      => '#000000aa',
        'guest'      => '#333333aa',
        'guest_nice' => '#aaaaaaaa',
        'system'     => '#F9AF62aa',
        'user'       => '#F9E962aa',
        'idle'       => '#44bb7799',
    ];

    protected $whiteParts = [
        'iowait'     => '#FFFFFF55',
        'softirq'    => '#FFFFFF66',
        'irq'        => '#FFFFFF77',
        'nice'       => '#FFFFFF88',
        'steal'      => '#FFFFFF99',
        'guest'      => '#FFFFFFAA',
        'guest_nice' => '#FFFFFFBB',
        'system'     => '#FFFFFFCC',
        'user'       => '#FFFFFFDD',
        'idle'       => '#ffffff00',
    ];

    protected $blackParts = [
        'iowait'     => '#00000055',
        'softirq'    => '#00000066',
        'irq'        => '#00000077',
        'nice'       => '#00000088',
        'steal'      => '#00000099',
        'guest'      => '#000000AA',
        'guest_nice' => '#000000BB',
        'system'     => '#000000CC',
        'user'       => '#000000DD',
        'idle'       => '#00000000',
    ];

    protected $sums = [
        'def_average_iowait',
        'def_average_softirq',
        'def_average_irq',
        'def_average_nice',
        'def_average_steal',
        'def_average_guest',
        'def_average_guest_nice',
        'def_average_system',
        'def_average_user',
    ];

    protected $max = 100;

    protected function sum(RrdGraph $graph, $defs, $preferredAlias)
    {
        return $graph->cdef(
            \implode(',', $defs) . str_repeat(',+', count($defs) - 1),
            $preferredAlias
        );
    }

    protected function sumNan(RrdGraph $graph, $defs, $preferredAlias)
    {
        return $graph->cdef(
            \implode(',', $defs) . str_repeat(',ADDNAN', count($defs) - 1),
            $preferredAlias
        );
    }

    protected function disableDs($dsName)
    {
        $pos = \array_search("def_average_$dsName", $this->sums);
        if ($pos !== false) {
            unset($this->sums[$pos]);
        }
    }

    public function applyToGraph(RrdGraph $graph)
    {
        parent::applyToGraph($graph);

        $sum = $this->sum($graph, $this->sums, 'total');
        $this->showTrend($graph, $sum);
    }

    protected function getParts()
    {
        if ($this->getParam('onlyWhite')) {
            $parts = $this->whiteParts;
        } elseif ($this->getParam('onlyBlack')) {
            $parts = $this->blackParts;
        } else {
            $parts = $this->parts;
        }

        foreach ($this->disabledDsNames as $dsName) {
            unset($parts[$dsName]);
        }

        return $parts;
    }
}
