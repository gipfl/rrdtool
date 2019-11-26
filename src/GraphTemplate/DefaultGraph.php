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
        $ds = $this->getParam('ds', 0); // Default DS
        $rra = $this->getParam('rra', 'AVERAGE');
        $showMaxPercentile = $this->getParam('maxPercentile', false); // default 100?
        $smoke = $this->getParam('smoke', false);
        $color = $this->getParam('color', $this->color);

        if (null !== $value = $this->getParam('lowerLimit')) {
            $graph->setLowerLimit($value);
        }
        if (null !== $value = $this->getParam('warningRule')) {
            $graph->addWarningRule($value);
        }
        if (null !== $value = $this->getParam('criticalRule')) {
            $graph->addCriticalRule($value);
        }
        // $graph->addPacketLoss($file, 2);

        if ($smoke) {
            $this->addSmoke($graph, $filename, $ds, $color, $showMaxPercentile);
        } else {
            $def = $graph->def($filename, $ds, $rra);
            $graph->area($def, $color, $ds !== 1);
        }
    }
}
