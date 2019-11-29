<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * man rrdgraph_graph
 * ------------------
 * Draw a horizontal line at value. HRULE acts much like LINE except that will
 * have no effect on the scale of the graph. If a HRULE is outside the graphing
 * area it will just not be visible and it will not appear in the legend by
 * default.
 *
 * Synopsis
 * --------
 * HRULE:value#color[:[legend][:dashes[=on_s[,off_s[,on_s,off_s]...]][:dash-offset=offset]]]
 */
class HRule extends DefinitionBasedInstruction
{
    use Dashes;

    protected $tag = 'HRULE';

    public function render()
    {
        return $this->tag
            . ':'
            . $this->getDefinition()
            . $this->getColor()
            . $this::optionalParameter($this::string($this->getLegend()))
            . $this->renderDashProperties();
    }
}
