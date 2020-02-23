<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class LoadGraph extends StackedGraph
{
    protected $parts = [
        'load15' => '#F96266aa',
        'load5'  => '#F9AF62aa',
        'load1'  => '#F9E962aa',
    ];
}
