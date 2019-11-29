<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * Graph the following elements with a specified time offset
 *
 * man rrdgraph_graph
 * ------------------
 * Using this command RRDtool will graph the following elements with the
 * specified offset. For instance, you can specify an offset of
 * ( 7*24*60*60 = ) 604'800 seconds to "look back" one week. Make sure to tell
 * the viewer of your graph you did this ...
 *
 * As with the other graphing elements, you can specify a number or a variable
 * here.
 *
 * Synopsis
 * --------
 * SHIFT:vname:offset
 */
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
