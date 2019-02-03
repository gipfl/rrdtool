<?php

namespace gipfl\RrdTool\GraphTemplate;

use gipfl\RrdTool\RrdGraph;

class VmwareDiskSeeksGraph extends Template
{
    public function applyToGraph(RrdGraph $graph)
    {
        $filename = $this->filename;

        $colors = [
            'smallSeeks'  => '57985B80',
            'mediumSeeks' => 'FFED5880',
            'largeSeeks'  => 'FFBF5880',
        ];
        foreach ($colors as $def => $color) {
            $gdef = $graph->def($filename, $def, 'AVERAGE');
            $graph->area($gdef, $color, $def !== 'smallSeeks');
        }
        $graph->setLowerLimit(0);
    }
}
