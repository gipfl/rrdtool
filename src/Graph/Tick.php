<?php

namespace gipfl\RrdTool\Graph;

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
            . $this->definition
            . $this->color
            . $this->optionalParameter($this->fraction)
            . $this->optionalParameter($this->string($this->getLegend()));
    }
}
