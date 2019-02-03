<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Line;
use gipfl\RrdTool\Graph\Tick;
use gipfl\RrdTool\RrdGraph;

class VmwareDiskReadWritesGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $read = $graph->def($filename, 'numberReadAveraged', 'AVERAGE');
        $write = $graph->def($filename, 'numberWriteAveraged', 'AVERAGE');
        $write = $graph->cdef("$write,-1,*");
        // $this->addSmoke($graph, $filename, 'numberReadAveraged', '57985B');
        $graph->area($read, '57985B');
        $graph->area($write, '0095BF');
        $graph->setLowerLimit(null);
        // $graph->setLowerLimit(null);


        return;
        // Forecast:

        $value = $read;
        $predict = $graph->cdef("86400,-7,1800,$value,PREDICT");
        $sigma = $graph->cdef("86400,-7,1800,$value,PREDICTSIGMA");
        $upper = $graph->cdef("$predict,$sigma,3,*,+");
        // $lower = $graph->cdef("$predict,$sigma,3,*,-");
        $lower = $graph->cdef("$predict,$sigma,3,*,-,0,MAX");
        $graph->add([
            new Line($predict, '00ff0044', 'prediction'),
            new Line($upper, '0000ff66', 'upper certainty limit'),
            new Line($lower, '0000ff66', 'lower certainty limit'),
        ]);


        $exceeds = $graph->cdef("$value,UN,0,$value,$lower,$upper,LIMIT,UN,IF");
        $graph->add((new Tick($exceeds, 'aa000080'))->setFraction(-0.05)); // setFraction(1);

        // $perc95 = $graph->cdef("86400,-7,1800,95,$value,PREDICTPERC");
        // $graph->add((new Line($perc95, '#ffff00', '95th percentile'))->setWidth(0.5));

        $ok = $graph->cdef("$upper,$read,LT,$read,$upper,IF");
        $nok = $graph->cdef("$upper,$read,LT,$read,0,IF");
        $graph->area($read, '57985B');
        $graph->add(new Line($nok, 'ff000000'));

//    TICK:exceeds#aa000080:1 \
//    CDEF:perc95=86400,-7,1800,95,value,PREDICTPERC \
//        LINE1:perc95#ffff00:95th_percentile
    }
}
