<?php

namespace gipfl\RrdTool\Graph;

class HRule extends DefinitionBasedInstruction
{
    use Dashes;

    public function render()
    {
        return 'HRULE:'
            . $this->definition
            . $this->color
            . $this->optionalParameter($this->string($this->getLegend()))
            . $this->renderDashProperties();
    }
}
