<?php

namespace gipfl\RrdTool\Graph\Instruction;

class Shift extends Instruction
{
    /** @var string */
    protected $variableName;

    /** @var int */
    protected $offset;

    public function __construct($vname, $offset)
    {
        $this->setVariableName($vname);
        $this->setOffset($offset);
    }

    /**
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

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function render()
    {
        return 'SHIFT:' . $this->getVariableName() . ':' . $this->getOffset();
    }
}
