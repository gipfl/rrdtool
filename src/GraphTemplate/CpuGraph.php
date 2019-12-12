<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class CpuGraph extends StackedGraph
{
    protected $parts = [
        'iowait'     => '#F96266cc',
        'softirq'    => '#F962F5cc',
        'irq'        => '#8362F9cc',
        'nice'       => '#D48823cc',
        'steal'      => '#000000cc',
        'guest'      => '#333333cc',
        'guest_nice' => '#aaaaaacc',
        'system'     => '#F9AF62cc',
        'user'       => '#F9E962cc',
        'idle'       => '#44bb77cc',
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

    public function applyToGraph(RrdGraph $graph)
    {
        parent::applyToGraph($graph);

        $sum = $this->sum($graph, [
            'def_average_iowait',
            'def_average_softirq',
            'def_average_irq',
            'def_average_nice',
            'def_average_steal',
            'def_average_guest',
            'def_average_guest_nice',
            'def_average_system',
            'def_average_user',
        ], 'total');
    }

    protected function getParts()
    {
        if ($this->getParam('onlyWhite')) {
            return $this->whiteParts;
        } elseif ($this->getParam('onlyBlack')) {
            return $this->blackParts;
        } else {
            return $this->parts;
        }
    }
}
