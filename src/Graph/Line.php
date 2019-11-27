<?php

namespace gipfl\RrdTool\Graph;

class Line extends DefinitionBasedInstruction
{
    use Dashes;

    /** @var float */
    protected $width = 1;

    /** @var bool */
    protected $stack = false;

    /** @var bool */
    protected $skipScale = false;

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
