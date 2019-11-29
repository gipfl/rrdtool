<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * If the optional STACK modifier is used, this line/area is stacked on top of
 * the previous element which can be a LINE or an AREA.
 */
trait Stack
{
    /** @var bool */
    protected $stack = false;

    /**
     * @return bool
     */
    public function isStack()
    {
        return $this->stack;
    }

    /**
     * @param bool $stack
     * @return $this
     */
    public function setStack($stack = true)
    {
        $this->stack = (bool) $stack;
        return $this;
    }
}
