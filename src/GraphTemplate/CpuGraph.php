<?php

namespace gipfl\RrdTool\GraphTemplate;

class CpuGraph extends StackedGraph
{
    protected $parts = [
        'iowait'     => '#F96266',
        'softirq'    => '#F962F5',
        'irq'        => '#8362F9',
        'nice'       => '#D48823',
        'steal'      => '#000000',
        'guest'      => '#333333',
        'guest_nice' => '#aaaaaa',
        'system'     => '#F9AF62',
        'user'       => '#F9E962',
        'idle'       => '#44bb77',
    ];
}
