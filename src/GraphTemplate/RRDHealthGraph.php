<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class RRDHealthGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;
        $disabled = \array_map('strtolower', $this->getArrayParam('disableDatasources'));

        if (! in_array('succeeded', $disabled)) {
            $this->simpleSmoke($graph, $filename, 'succeeded', '#44bb77');
        }
        if (! in_array('invalid', $disabled)) {
            $this->simpleSmoke($graph, $filename, 'invalid', '#F96266');
        }
        if (! in_array('deferred', $disabled)) {
            $this->simpleSmoke($graph, $filename, 'deferred', '#7A64C8', -1);
        }
    }
}
