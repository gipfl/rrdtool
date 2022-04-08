<?php

namespace gipfl\RrdTool\RenderedGraph;

use JsonSerializable;

class GraphInfoImage implements JsonSerializable
{
    protected int $width;
    protected int $height;

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): GraphInfoImage
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): GraphInfoImage
    {
        $this->height = $height;
        return $this;
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'width'  => $this->width,
            'height' => $this->height,
        ];
    }
}
