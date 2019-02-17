<?php

namespace gipfl\RrdTool\Graph;

class Tick extends Instruction
{
    use Dashes;

    /** @var string */
    protected $definition;

    /** @var float */
    protected $fraction;

    /** @var Color */
    protected $color;

    /** @var string|null */
    protected $legend;


    /** @var bool */
    protected $skipScale = false;

    /**
     * Area constructor.
     * @param string $definition
     * @param Color|string $color
     * @param float $fraction
     * @param string $legend
     */
    public function __construct($definition, $color = null, $fraction = null, $legend = null)
    {
        $this->definition = (string) $definition;
        $this->color = $this->wantColor($color);
        $this->setFraction($fraction);
        $this->legend = $legend;
    }

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

    /**
     * @return string|null
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * @param string|null $legend
     * @return $this
     */
    public function setLegend($legend)
    {
        $this->legend = (string) $legend;
        return $this;
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
