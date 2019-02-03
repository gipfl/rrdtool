<?php

namespace gipfl\RrdTool\Graph;

class HRule extends Instruction
{
    use Dashes;

    /** @var string */
    protected $definition;

    /** @var Color */
    protected $color;

    /** @var string|null */
    protected $legend;

    /**
     * Area constructor.
     * @param string $definition
     * @param Color|string $color
     * @param string $legend
     */
    public function __construct($definition, $color = null, $legend = null)
    {
        $this->definition = (string) $definition;
        $this->color = $this->wantColor($color);
        $this->legend = $legend;
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
        return 'HRULE:'
            . $this->definition
            . $this->color
            . $this->optionalParameter($this->string($this->getLegend()))
            . $this->renderDashProperties();
    }
}
