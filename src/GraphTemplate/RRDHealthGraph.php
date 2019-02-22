<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class RRDHealthGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        $this->simpleSmoke($graph, $filename, 'succeeded', '#44bb77');
        $this->simpleSmoke($graph, $filename, 'invalid', '#F96266');
        $this->simpleSmoke($graph, $filename, 'deferred', '#7A64C8', -1);
    }
}
