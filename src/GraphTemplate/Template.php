<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Line;
use gipfl\RrdTool\RrdGraph;

abstract class Template
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    abstract public function applyToGraph(RrdGraph $graph);

    protected function addSmoke(RrdGraph $graph, $file, $dsname, $color = '0095BF', $showMaxPercentile = 100)
    {
        $steps = 10;
        $avg = $graph->def($file, $dsname, 'AVERAGE');
        $min = $graph->def($file, $dsname, 'MIN');
        $max = $graph->def($file, $dsname, 'MAX');
        $avgStep = $graph->cdef("$avg,$min,-,$steps,/");
        $maxStep = $graph->cdef("$max,$avg,-,$steps,/");
        $pctMax = $graph->vdef("$max,$showMaxPercentile,PERCENT");

        $graph->add((new Area($min))->setSkipScale());
        $grad = (80 / $steps);

        $stepColor = new Color($color);
        for ($i = 1; $i <= $steps; $i++) {
            $alpha = sprintf('%02x', floor($grad * $i));
            $graph->add((new Area($avgStep, $stepColor->setAlphaHex($alpha)))->setStack());
        }
        for ($i = $steps; $i > 0; $i--) {
            $alpha = sprintf('%02x', floor($grad * $i));
            $area = new Area($maxStep, $stepColor->setAlphaHex($alpha));
            $area->setStack()->setSkipScale();
            $graph->add($area);
        }

        $legend = '';
        $graph->add(new Line($avg, $color, $legend));
        $graph->add(new Line($pctMax));
    }
}
