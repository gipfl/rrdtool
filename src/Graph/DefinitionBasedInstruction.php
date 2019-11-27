<?php

namespace gipfl\RrdTool\Graph;

abstract class DefinitionBasedInstruction extends Instruction
{
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
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param string $definition
     * @return DefinitionBasedInstruction
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Color $color
     * @return DefinitionBasedInstruction
     */
    public function setColor($color)
    {
        $this->color = $this->wantColor($color);
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
}