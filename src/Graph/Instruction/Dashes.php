<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * The dashes modifier enables dashed line style. Without any further
 * options a symmetric dashed line with a segment length of 5 pixels will
 * be drawn.
 */
trait Dashes
{
    /** @var string|null */
    protected $dashes;

    /** @var string|null */
    protected $dashOffset;

    /**
     * @return string|null
     */
    public function getDashes()
    {
        return $this->dashes;
    }

    /**
     * The dash pattern can be changed if the dashes= parameter is followed by
     * either one value or an even number (1, 2, 4, 6, ...) of positive values.
     * Each value provides the length of alternate on_s and off_s portions of
     * the stroke.

     * @param string|null $dashes
     * @return $this
     */
    public function setDashes($dashes)
    {
        $this->dashes = $dashes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDashOffset()
    {
        return $this->dashOffset;
    }

    /**
     * The dash-offset parameter specifies an offset into the pattern at which
     * the stroke begins.
     *
     * @param string|null $dashOffset
     * @return $this
     */
    public function setDashOffset($dashOffset)
    {
        $this->dashOffset = $dashOffset;
        return $this;
    }

    /**
     * @return string
     */
    protected function renderDashProperties()
    {
        return Instruction::optionalNamedParameter('dashes', $this->getDashes())
            . Instruction::optionalNamedParameter('dash-offset', $this->getDashOffset());
    }
}
