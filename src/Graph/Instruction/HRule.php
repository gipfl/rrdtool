<?php

namespace gipfl\RrdTool\Graph\Instruction;

class HRule extends DefinitionBasedInstruction
{
    use Dashes;

    public function render()
    {
        return 'HRULE:'
            . $this->definition
            . $this->getColor()
            . $this::optionalParameter($this::string($this->getLegend()))
            . $this->renderDashProperties();
    }
}
