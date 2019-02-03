<?php

namespace gipfl\RrdTool\Graph;

use InvalidArgumentException;

class Area extends Instruction
{
    /** @var string */
    protected $definition;

    /** @var Color */
    protected $color;

    /** @var Color */
    protected $color2;

    /** @var string|null */
    protected $legend;

    /** @var bool */
    protected $stack = false;

    /** @var bool */
    protected $skipScale = false;

    /** @var string|null */
    protected $gradientHeight;

    /**
     * Area constructor.
     * @param string $definition
     * @param Color|string $color
     */
    public function __construct($definition, $color = null, $legend = null)
    {
        $this->definition = (string) $definition;
        $this->color = $this->wantColor($color);
        $this->legend = $legend;
    }

    /**
     * @param Color|string $color
     * @return $this
     */
    public function setSecondColor($color)
    {
        if ($this->color->isNull()) {
            throw new InvalidArgumentException('Cannot set #color2 when no #color has been given');
        }
        $this->color2 = $this->wantColor($color);

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
     * @return Area
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
     * @return Area
     */
    public function setStack($stack = true)
    {
        $this->stack = (bool) $stack;
        return $this;
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
     * @return Area
     */
    public function setSkipScale($skipScale = true)
    {
        $this->skipScale = (bool) $skipScale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGradientHeight()
    {
        return $this->gradientHeight;
    }

    /**
     * @param string|null $gradientHeight
     * @return Area
     */
    public function setGradientHeight($gradientHeight)
    {
        $this->gradientHeight = $gradientHeight;
        return $this;
    }

    public function render()
    {
        $string = 'AREA:'
            . $this->definition
            . $this->color
            . $this->color2
            . $this->optionalParameter($this->string($this->getLegend()));
        if ($this->isStack()) {
            $string .= ':STACK';
        }

        if ($this->isSkipScale()) {
            $string .= ':skipscale';
        }

        $string .= $this->optionalNamedParameter('gradheight', $this->getGradientHeight());

        return $string;
    }
}
