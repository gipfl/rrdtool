<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\HRule;
use gipfl\RrdTool\RrdGraph;

class InterfaceGraph extends Template
{
    protected $dsRx = 'rxOctets';

    protected $dsTx = 'txOctets';

    protected $colorRx = '57985B';

    protected $colorTx = '0095BF';

    protected $multiplier = 8;

    public function __construct($filename, $dsRx = null, $dsTx = null)
    {
        parent::__construct($filename);
        if ($dsRx !== null) {
            $this->dsRx = $dsRx;
        }
        if ($dsTx !== null) {
            $this->dsTx = $dsTx;
        }
    }

    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        $dsRx = $this->dsRx;
        $dsTx = $this->dsTx;

        $this->addBytesAsBitsPerSecond($graph, $filename, $dsRx, $this->colorRx, false);
        $this->addBytesAsBitsPerSecond($graph, $filename, $dsTx, $this->colorTx, true);
        $this->addHiddenMax($graph, $filename, $dsRx, 'MAX', false);
        $this->addHiddenMax($graph, $filename, $dsRx, 'MAX', true);
        $this->addHiddenMax($graph, $filename, $dsTx, 'MAX', false);
        $this->addHiddenMax($graph, $filename, $dsTx, 'MAX', true);
        $this->addSummary($graph);
    }

    protected function addSummary(RrdGraph $graph)
    {
        $filename = $this->filename;
        $dsRx = $this->dsRx;
        $dsTx = $this->dsTx;
        $rx = $graph->def($filename, $dsRx, 'AVERAGE');
        $tx = $graph->def($filename, $dsTx, 'AVERAGE');
        $rxMax = $graph->def($filename, $dsRx, 'MAX');
        $txMax = $graph->def($filename, $dsTx, 'MAX');
        $rxTotal = $graph->vdef("$rx,TOTAL", 'rxTotal');
        $graph->printDef($rxTotal, '%5.4lf', 'Received');
        $txTotal = $graph->vdef("$tx,TOTAL", 'txTotal');
        $graph->printDef($txTotal, '%5.4lf', 'Transmitted');
        $rxHighest = $graph->vdef("$rxMax,MAXIMUM", 'rxHighest');
        $graph->printDef($rxHighest, '%5.4lf', 'Peak RX');
        $txHighest = $graph->vdef("$txMax,MAXIMUM", 'rxHighest');
        $graph->printDef($txHighest, '%5.4lf', 'Peak TX');
        $rxAverage = $graph->vdef("$rx,AVERAGE", 'rxAverage');
        $graph->printDef($rxAverage, '%5.4lf', 'Average RX');
        $txAverage = $graph->vdef("$tx,AVERAGE", 'txAverage');
        $graph->printDef($txAverage, '%5.4lf', 'Average TX');
    }

    public function addHiddenMax(RrdGraph $graph, $filename, $ds, $cf = 'MAX', $mirrored = false)
    {
        $def = $graph->def($filename, $ds, $cf);
        $multiplier = $this->multiplier;
        $defMax = $graph->cdef("$def,$multiplier,*");

        if ($mirrored) {
            $neg = $graph->cdef("$defMax,-1,*");
            $minimum = $graph->vdef("$neg,AVERAGE");
            $maximum = $graph->vdef("$defMax,AVERAGE");
            $graph->line1($maximum);
            $graph->line1($minimum);
        } else {
            $maximum = $graph->vdef("$defMax,AVERAGE");
            $graph->line1($maximum);
        }
    }

    public function addSmoked(RrdGraph $graph, $filename, $ds, $color, $steps = 1)
    {
        $color = new Color($color);
        $defAvg = $graph->def($filename, $ds, 'AVERAGE');
        $defMin = $graph->def($filename, $ds, 'MIN');
        $defMax = $graph->def($filename, $ds, 'MAX');
        $multiplier = $this->multiplier;
        $defAvg = $graph->cdef("$defAvg,$multiplier,*");
        $defMin = $graph->cdef("$defMin,$multiplier,*");
        $defMax = $graph->cdef("$defMax,$multiplier,*");
        $avgStep = $graph->cdef("${defAvg},${defMin},-,${steps},/", 'avgstep');
        $maxStep = $graph->cdef("${defMax},${defAvg},-,${steps},/", 'maxstep');
        // TODO: class for Datasource, allow to mirror ...
    }

    public function addBytesAsBitsPerSecond(RrdGraph $graph, $filename, $ds, $color, $negate = false)
    {
        $defAvg = $graph->def($filename, $ds, 'AVERAGE');
        $defMin = $graph->def($filename, $ds, 'MIN');
        $defMax = $graph->def($filename, $ds, 'MAX');
        $multiplier = $this->multiplier;
        $defAvg = $graph->cdef("$defAvg,$multiplier,*");
        $defMin = $graph->cdef("$defMin,$multiplier,*");
        $defMax = $graph->cdef("$defMax,$multiplier,*");
        $steps = 1;
        $avgStep = $graph->cdef("${defAvg},${defMin},-,${steps},/", 'avgstep');
        $maxStep = $graph->cdef("${defMax},${defAvg},-,${steps},/", 'maxstep');

        if ($negate) {
            $defAvg = $graph->cdef("${defAvg},-1,*", 'negAvg');
            $defMin = $graph->cdef("${defMin},-1,*", 'negMin');
            $avgStep = $graph->cdef("${avgStep},-1,*", 'negAvgStep');
            $maxStep = $graph->cdef("${maxStep},-1,*", 'negMaxStep');
        }

        $graph->add((new HRule(0, $graph->getTextColor()->setAlphaHex('80'))));
        $showAllMaxValues = false;

        // empty unless min
        $graph->add((new Area($defMin))->setSkipScale());
        $grad = (80 / $steps);

        $stepColor = new Color($color);
        for ($i = 1; $i <= $steps; $i++) {
            $alpha = sprintf('%02x', floor($grad * $i));
            $graph->add((new Area($avgStep, $stepColor->setAlphaHex($alpha)))->setStack());
        }
        for ($i = $steps; $i > 0; $i--) {
            $alpha = sprintf('%02x', floor($grad * $i));
            $area = new Area($maxStep, $stepColor->setAlphaHex($alpha));
            $area->setStack();
            if (! $showAllMaxValues) {
                $area->setSkipScale();
            }
            $graph->add($area);
        }
        // The real line
        $graph->line1($defAvg, $color . 'ff');
        $graph->add(new Area($defAvg, "${color}33"));
    }
}
