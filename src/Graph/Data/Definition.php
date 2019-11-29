<?php

namespace gipfl\RrdTool\Graph\Data;

use gipfl\RrdTool\Graph\Instruction\Instruction;

abstract class Definition extends Instruction
{
    /** @var string */
    protected $variableName;

    /**
     * A-Z, a-z, 0-9, _, -
     * Do not choose a name that is already taken by an RPN operator
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @param string $variableName
     * @return $this
     */
    public function setVariableName($variableName)
    {
        $this->variableName = $variableName;
        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }
}
