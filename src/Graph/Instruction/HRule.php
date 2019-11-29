<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
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
