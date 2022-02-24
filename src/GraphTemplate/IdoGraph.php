<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class IdoGraph extends StackedGraph
{
    protected $parts = [
        'queries' => '#F96266aa',
        'pending_queries'  => '#F9AF62aa',
    ];
}
