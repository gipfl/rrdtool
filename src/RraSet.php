<?php

namespace gipfl\RrdTool;

class RraSet
{
    /** @var Rra[] */
    protected $rras = [];

    public function __construct($rras)
    {
        foreach ($rras as $rra) {
            if ($rra instanceof Rra) {
                $this->rras[] = $rra;
            } else {
                $this->rras[] = Rra::fromString($rra);
            }
        }
    }

    public static function fromString($str)
    {
        return new static(\preg_split('/ /', $str));
    }

    public function getRras()
    {
        return $this->rras;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return \implode(' ', $this->rras);
    }

    /**
     * @return int
     */
    public function getDataSize()
    {
        $size = 0;
        foreach ($this->rras as $rra) {
            $size += $rra->getDataSize();
        }

        return $size;
    }

    public function getRraByIndex($index)
    {
        if (isset($this->rras[$index])) {
            return $this->rras[$index];
        } else {
            throw new \InvalidArgumentException("There is no RRA at index '$index'");
        }
    }

    public function getLongestRra()
    {
        return $this->getRraByIndex($this->getIndexForLongestRra());
    }

    public function getIndexForLongestRra()
    {
        $maxPdp = 0;
        $rraIdx = 0;
        // $oldestPossible = 0;
        /** @var Rra $rra */
        foreach ($this->rras as $idx => $rra) {
            // TODO: what about RraForecasting?
            if ($rra instanceof RraAggregation) {
                $curPdp = $rra->getRows() * $rra->getSteps();
                if ($curPdp > $maxPdp) {
                    $maxPdp = $curPdp;
                    $rraIdx = $idx;
                }
            }
        }

        return $rraIdx;
    }
}
