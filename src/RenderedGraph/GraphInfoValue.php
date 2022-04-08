<?php

namespace gipfl\RrdTool\RenderedGraph;

use JsonSerializable;

class GraphInfoValue implements JsonSerializable
{
    /** @var int|float */
    protected $min;

    /** @var int|float */
    protected $max;

    /**
     * @return float|int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param float|int $min
     * @return GraphInfoValue
     */
    public function setMin($min): GraphInfoValue
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param float|int $max
     * @return GraphInfoValue
     */
    public function setMax($max): GraphInfoValue
    {
        $this->max = $max;
        return $this;
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
