<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Instruction\Area;
use gipfl\RrdTool\Graph\Instruction\Line;
use gipfl\RrdTool\RrdGraph;

abstract class Template
{
    protected $filename;

    protected $params;

    public function __construct($filename, $params = null)
    {
        $this->filename = $filename;
        if ($params === null) {
            $this->setParams([]);
        } else {
            $this->setParams($params);
        }
    }

    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    protected function getParam($name, $default = null)
    {
        if (\array_key_exists($name, $this->params)) {
            return $this->params[$name];
        } else {
            return $default;
        }
    }

    protected function getArrayParam($name, $default = [])
    {
        if (\array_key_exists($name, $this->params)) {
            return (array) $this->params[$name];
        } else {
            return $default;
        }
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
            $alpha = \sprintf('%02x', floor($grad * $i));
            $graph->add((new Area($avgStep, $stepColor->setAlphaHex($alpha)))->setStack());
        }
        for ($i = $steps; $i > 0; $i--) {
            $alpha = \sprintf('%02x', floor($grad * $i));
            $area = new Area($maxStep, $stepColor->setAlphaHex($alpha));
            $area->setStack()->setSkipScale();
            $graph->add($area);
        }

        $legend = '';
        $graph->add(new Line($avg, $color, $legend));
        $graph->add(new Line($pctMax));
    }

    protected function simpleSmoke(RrdGraph $graph, $file, $ds, $color, $multiplier = null)
    {
        if ($multiplier === null) {
            $multi = '';
        } else {
            $multi = ",$multiplier,*";
        }
        $minDef  = $graph->def($file, $ds, 'MIN');
        $minCdef = $graph->cdef("$minDef$multi");
        $graph->add(new Area($minCdef));
        $maxDef  = $graph->def($file, $ds, 'MAX');
        $maxCdef = $graph->cdef("$maxDef,$minDef,-$multi");
        $graph->add((new Area($maxCdef, new Color($color, '66')))->setStack());
        $avgDef  = $graph->def($file, $ds, 'AVERAGE');
        $avgCdef = $graph->cdef("$avgDef$multi");
        $graph->add(new Line($avgCdef, new Color($color)));
    }

    protected function showTrend(RrdGraph $graph, $def, $time = 3600, $color = '#0095BF66')
    {
        $trend = $graph->cdef("$def,$time,TRENDNAN", 'trend_smoothed');
        $trendLine = new Line($trend, $color);
        $graph->add($trendLine->setWidth(1.5));
    }
}
