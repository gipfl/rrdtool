<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * Normally the graphing function makes sure that the entire LINE or AREA
 * is visible in the chart. The scaling of the chart will be modified
 * accordingly if necessary. Any LINE or AREA can be excluded from this
 * process by adding the option skipscale.
 */
trait SkipScale
{
    /** @var bool */
    protected $stack = false;

    /** @var bool */
    protected $skipScale = false;

    /**
     * @return bool
     */
    public function isSkipScale()
    {
        return $this->skipScale;
    }

    /**
     * @param bool $skipScale
     * @return $this
     */
    public function setSkipScale($skipScale = true)
    {
        $this->skipScale = (bool) $skipScale;
        return $this;
    }
}
