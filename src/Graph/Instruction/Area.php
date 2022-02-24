<?php declare(strict_types=1);

namespace gipfl\RrdTool\Graph\Instruction;

use gipfl\RrdTool\Graph\Color;
use InvalidArgumentException;

/**
 * Draw a filled area
 *
 * man rrdgraph_graph
 * ------------------
 * See LINE, however the area between the x-axis and the line will be filled.
 *
 * Synopsis
 * --------
 * AREA:value[#color][:[legend][:STACK][:skipscale]]
 */
class Area extends DefinitionBasedInstruction
{
    use SkipScale;
    use Stack;

    /** @var Color|null */
    protected $color2;

    /** @var string|null */
    protected $gradientHeight;

    /**
     * @return Color|null
     */
    public function getSecondColor(): ?Color
    {
        return $this->color2;
    }

    /**
     * If color2 is specified, the area will be filled with a gradient
     *
     * @param Color|string $color
     * @return $this
     */
    public function setSecondColor($color): Area
    {
        if ($this->color->isNull()) {
            throw new InvalidArgumentException('Cannot set #color2 when no #color has been given');
        }
        $this->color2 = Color::create($color);

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
     * The gradheight parameter can create three different behaviors. If
     * gradheight > 0, then the gradient is a fixed height, starting at the
     * line going down. If gradheight < 0, then the gradient starts at a fixed
     * height above the x-axis, going down to the x-axis. If height == 0, then
     * the gradient goes from the line to x-axis.
     *
     * The default value for gradheight is 50.
     *
     * @param string|null $gradientHeight
     * @return $this
     */
    public function setGradientHeight($gradientHeight)
    {
        $this->gradientHeight = $gradientHeight;
        return $this;
    }

    public function render()
    {
        return 'AREA:'
            . $this->getDefinition()
            . $this->getColor()
            . $this->getSecondColor()
            . $this::optionalParameter($this::string($this->getLegend()))
            . $this::optionalNamedBoolean('STACK', $this->isStack())
            . $this::optionalNamedBoolean('skipscale', $this->isSkipScale())
            . $this::optionalNamedParameter('gradheight', $this->getGradientHeight())
        ;
    }
}
