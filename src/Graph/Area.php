<?php

namespace gipfl\RrdTool\Graph;

use InvalidArgumentException;

class Area extends DefinitionBasedInstruction
{
    /** @var Color */
    protected $color2;

    /** @var bool */
    protected $stack = false;

    /** @var bool */
    protected $skipScale = false;

    /** @var string|null */
    protected $gradientHeight;

    /**
     * @param Color|string $color
     * @return $this
     */
    public function setSecondColor($color)
    {
        if ($this->color->isNull()) {
            throw new InvalidArgumentException('Cannot set #color2 when no #color has been given');
        }
        $this->color2 = Color::create($color);

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
