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
}
