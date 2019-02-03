<?php

namespace gipfl\RrdTool\Graph;

class Line extends Instruction
{
    use Dashes;

    /** @var string */
    protected $definition;

    /** @var float */
    protected $width = 1;

    /** @var Color */
    protected $color;

    /** @var string|null */
    protected $legend;

    /** @var bool */
    protected $stack = false;

    /** @var bool */
    protected $skipScale = false;

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
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
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

    /**
     * @return string
     */
    protected function renderStack()
    {
        return $this->isStack() ? ':STACK' : '';
    }

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

    /**
     * @return string
     */
    protected function renderSkipScale()
    {
        return $this->isSkipScale() ? ':skipscale' : '';
    }

    public function render()
    {
        return 'LINE'
            . sprintf('%.3G', $this->getWidth())
            . ':'
            . $this->definition
            . $this->color
            . $this->optionalParameter($this->string($this->getLegend()))
            . $this->renderStack()
            . $this->renderSkipScale()
            . $this->renderDashProperties();
    }
}
