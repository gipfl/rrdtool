<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\Graph\Instruction\Area;
use gipfl\RrdTool\Graph\Instruction\HRule;
use gipfl\RrdTool\RrdGraph;

class VmwareIfPacketsGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $defs = [
            'packetsRx' => $graph->def($filename, 'packetsRx', 'AVERAGE'),
            'multicastRx' => $graph->def($filename, 'multicastRx', 'AVERAGE'),
            'broadcastRx' => $graph->def($filename, 'broadcastRx', 'AVERAGE'),
            'droppedRx' => $graph->def($filename, 'droppedRx', 'AVERAGE'),
            'packetsTx' => $graph->def($filename, 'packetsTx', 'AVERAGE'),
            'multicastTx' => $graph->def($filename, 'multicastTx', 'AVERAGE'),
            'broadcastTx' => $graph->def($filename, 'broadcastTx', 'AVERAGE'),
            'droppedTx' => $graph->def($filename, 'droppedTx', 'AVERAGE'),
        ];
        $cdefs = [];
        foreach ($defs as $name => $def) {
            if (substr($name, -2) === 'Rx') {
                // $cdefs[$name] = $graph->cdef("$def,20,/"); ??
                $cdefs[$name] = $graph->cdef("$def,1,/");
            } else {
                $cdefs[$name] = $graph->cdef("$def,1,/,-1,*");
            }
        }
        foreach (['Rx', 'Tx'] as $dir) {
            $packets = $cdefs["packets${dir}"];
            $multicast = $cdefs["multicast${dir}"];
            $broadcast = $cdefs["broadcast${dir}"];
            $unicast = $graph->cdef("$packets,$multicast,-,$broadcast,-");
            $cdefs["unicast${dir}"] = $unicast;
        }
        // Stack, order matters!
        $colors = [
            'unicastRx' => '57985B',
            'broadcastRx' => 'EE55FF',
            'multicastRx' => 'FF9933',
            'droppedRx' => 'FF5555',
            'unicastTx' => '0095BF',
            'broadcastTx' => 'FF99FF',
            'multicastTx' => 'FFCC99',
            'droppedTx' => 'FF9999',
        ];
        $graph->setLowerLimit(null);
        $graph->add((new HRule(0, $graph->getTextColor()->setAlphaHex('80'))));

        foreach ($colors as $name => $color) {
            if (\substr($name, 0, 7) === 'packets') {
                continue;
            }
            $line = new Area($cdefs[$name], $color . '99');
            if (substr($name, 0, 7) !== 'unicast') {
                $line->setStack();
            }

            $graph->add($line);
        }

        // $trend = $graph->cdef($cdefs['packetsRx'] . ",7200,TREND");
        // $graph->add((new Line($trend, $colors['unicastRx'] . '44'))->setWidth(3));
        // $trend = $graph->cdef($cdefs['packetsTx'] . ",7200,TREND");
        // $graph->add((new Line($trend, $colors['unicastTx'] . '44'))->setWidth(3));
    }
}
