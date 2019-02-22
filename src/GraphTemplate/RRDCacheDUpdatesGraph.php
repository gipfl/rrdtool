<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class RRDCacheDUpdatesGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        $this->simpleSmoke($graph, $filename, 'UpdatesReceived', '#63B4FF', -1);
        $this->simpleSmoke($graph, $filename, 'DataSetsWritten', '#30B85D');
        $this->simpleSmoke($graph, $filename, 'UpdatesWritten', '#F96266');
    }
}
