<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class DefaultGraph extends Template
{
    /** @var string  */
    protected $color = '0095BF'; // @icinga-blue

    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        // TODO: this is currently broken, we need parameters.
        $ds = $params->get('ds');
        $rra = $params->get('rra', 'AVERAGE');
        $showMaxPercentile = $params->get('maxPercentile', 100);
        $smoke = $params->get('smoke');
        $color = $params->get('color', $this->color);
        $graph->setLowerLimit(0);

        // $graph->addWarningRule(10);
        // $graph->addCriticalRule(20);
        // $graph->addPacketLoss($file, 2);

        if ($smoke) {
            $this->addSmoke($graph, $filename, $ds, $color, $showMaxPercentile);
        } else {
            $def = $graph->def($filename, $ds, $rra);
            $graph->area($def, $color, $ds !== 1);
        }
    }
}
