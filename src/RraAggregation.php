<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

class RraAggregation extends Rra
{
    protected static $functions = [
        'AVERAGE',
        'MIN',
        'MAX',
        'LAST'
    ];

    protected $xFilesFactor;

    protected $steps;

    protected $rows;

    public static function isKnown($name)
    {
        return \in_array($name, self::$functions);
    }

    /**
     * xff:steps:rows
     * @param $str
     */
    public function setArgumentsFromString($str)
    {
        $parts = \preg_split('/:/', $str);
        if (\count($parts) !== 3) {
            throw new InvalidArgumentException(
                "Expected 'xff:steps:rows' RRA aggregation function arguments, got '$str''"
            );
        }

        $this->xFilesFactor = $parts[0];
        $this->steps = $parts[1];
        $this->rows = $parts[2];
    }

    public function setArgumentsFromInfo(array $info)
    {
        $this->xFilesFactor = $info['xff'];
        $this->steps = $info['pdp_per_row'];
        $this->rows = $info['rows'];
    }

    public function toString()
    {
        return 'RRA:' . \implode(':', [
            $this->consolidationFunction,
            $this->xFilesFactor,
            $this->steps,
            $this->rows
        ]);
    }
}
