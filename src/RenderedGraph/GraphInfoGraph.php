<?php

namespace gipfl\RrdTool\RenderedGraph;

use JsonSerializable;

class GraphInfoGraph implements JsonSerializable
{
    protected int $left;
    protected int $top;
    protected int $width;
    protected int $height;
    protected int $start;
    protected int $end;

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): GraphInfoGraph
    {
        $this->left = $left;
        return $this;
    }

    public function getTop(): int
    {
        return $this->top;
    }

    public function setTop(int $top): GraphInfoGraph
    {
        $this->top = $top;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): GraphInfoGraph
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): GraphInfoGraph
    {
        $this->height = $height;
        return $this;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): GraphInfoGraph
    {
        $this->start = $start;
        return $this;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): GraphInfoGraph
    {
        $this->end = $end;
        return $this;
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'left'   => $this->left,
            'top'    => $this->top,
            'width'  => $this->width,
            'height' => $this->height,
            'start'  => $this->start,
            'end'    => $this->end,
        ];
    }
}
