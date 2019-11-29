<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * Plot tick marks
 *
 * man rrdgraph_graph
 * ------------------
 * Plot a tick mark (a vertical line) for each value of vname that is non-zero
 * and not *UNKNOWN*.
 *
 * Note that the color specification is not optional
 *
 * Synopsis
 * --------
 * TICK:vname#rrggbb[aa][:fraction[:legend]]
 */
class Tick extends DefinitionBasedInstruction
{
    use Dashes;

    /** @var float */
    protected $fraction;

    /** @var bool */
    protected $skipScale = false;

    /**
     * @return float
     */
    public function getFraction()
    {
        return $this->fraction;
    }

    /**
     * The fraction argument specifies the length of the tick mark as a fraction
     * of the y-axis; the default value is 0.1 (10% of the axis)
     *
     * The TICK marks normally start at the lower edge of the graphing area. If
     * the fraction is negative they start at the upper border of the graphing
     * area.
     *
     * @param float $fraction
     * @return $this
     */
    public function setFraction($fraction)
    {
        $this->fraction = $fraction;
        return $this;
    }

    protected function renderFraction()
    {
        return $this->fraction === null ? '' : \sprintf('%.3G', $this->fraction);
    }

    public function render()
    {
        return 'TICK:'
            . $this->getDefinition()
            . $this->getColor()
            . $this::optionalParameter($this->renderFraction())
            . $this::optionalParameter($this::string($this->getLegend()));
    }
}
