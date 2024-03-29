<?php

namespace gipfl\RrdTool\GraphTemplate;

use Generator;
use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Instruction\Area;
use gipfl\RrdTool\Graph\Instruction\HRule;
use gipfl\RrdTool\RrdGraph;

class InterfaceGraph extends Template
{
    // protected $dsRx = 'rxOctets';
    protected $dsRx = 'rxBytes';

    // protected $dsTx = 'txOctets';
    protected $dsTx = 'txBytes';

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

    protected function avgMinMax(RrdGraph $graph, $filename, $ds)
    {
        return [
            $graph->def($filename, $ds, 'AVERAGE'),
            $graph->def($filename, $ds, 'MIN'),
            $graph->def($filename, $ds, 'MAX')
        ];
    }

    protected function multiply(RrdGraph $graph, $multiplier, $def)
    {
        return $graph->cdef("$def,$multiplier,*");
    }

    protected function multipleMultiply(RrdGraph $graph, $multiplier, ...$def)
    {
        $result = [];
        foreach ($def as $d) {
            $result[] = $this->multiply($graph, $multiplier, $d);
        }

        return $result;
    }

    public function addSmoked(RrdGraph $graph, $filename, $ds, $color, $steps = 1)
    {
        $color = new Color($color);
        list($defAvg, $defMin, $defMax) = $this->avgMinMax($graph, $filename, $ds);
        list($defAvg, $defMin, $defMax)
            = $this->multipleMultiply($graph, $this->multiplier, $defAvg, $defMin, $defMax);
        $avgStep = $graph->cdef("$defAvg,$defMin,-,$steps,/", 'avgstep');
        $maxStep = $graph->cdef("$defMax,$defAvg,-,$steps,/", 'maxstep');
        // TODO: class for Datasource, allow to mirror ...
    }

    public function addBytesAsBitsPerSecond(RrdGraph $graph, $filename, $ds, $color, $negate = false)
    {
        $color = new Color($color);
        list($defAvg, $defMin, $defMax) = $this->avgMinMax($graph, $filename, $ds);
        $multiplier = $this->multiplier;
        $defAvg = $graph->cdef("$defAvg,$multiplier,*");
        $defMin = $graph->cdef("$defMin,$multiplier,*");
        $defMax = $graph->cdef("$defMax,$multiplier,*");
        $steps = 1; // One step only, otherwise image get's overloaded. We might want to drop this
        $avgStep = $graph->cdef("$defAvg,$defMin,-,$steps,/", 'avgstep');
        $maxStep = $graph->cdef("$defMax,$defAvg,-,$steps,/", 'maxstep');

        if ($negate) {
            $defAvg = $graph->cdef("$defAvg,-1,*", 'negAvg');
            $defMin = $graph->cdef("$defMin,-1,*", 'negMin');
            $avgStep = $graph->cdef("$avgStep,-1,*", 'negAvgStep');
            $maxStep = $graph->cdef("$maxStep,-1,*", 'negMaxStep');
        }

        $graph->add((new HRule(0, $graph->getTextColor()->setAlphaHex('80'))));
        $showAllMaxValues = true;

        // empty unless min
        $min = new Area($defMin);
        if (! $showAllMaxValues) {
            // TODO: Check this:
            $min->setSkipScale();
        }
        $graph->add($min);
        foreach ($this->fadingStepsUp($color, $steps) as $stepColor) {
            $graph->add((new Area($avgStep, $stepColor))->setStack());
        }

        // $this->showTrend($graph, $defAvg, 86400, '#dd0000');
        foreach ($this->fadingStepsDown($color, $steps) as $stepColor) {
            $area = (new Area($maxStep, $stepColor))->setStack();
            if (! $showAllMaxValues) {
                $area->setSkipScale();
            }
            $graph->add($area);
        }

        // The real line
        $graph->line1($defAvg, $color . 'ff');
        $graph->add(new Area($defAvg, "{$color}33"));
    }

    protected function fadingStepsUp(Color $color, $steps): Generator
    {
        $grad = (80 / $steps);
        for ($i = 1; $i <= $steps; $i++) {
            $stepColor = clone($color);
            $alpha = \sprintf('%02x', floor($grad * $i));
            yield $stepColor->setAlphaHex($alpha);
        }
    }

    protected function fadingStepsDown(Color $color, $steps): Generator
    {
        $grad = (80 / $steps);
        for ($i = $steps; $i > 0; $i--) {
            $stepColor = clone($color);
            $alpha = \sprintf('%02x', floor($grad * $i));
            yield $stepColor->setAlphaHex($alpha);
        }
    }
}
