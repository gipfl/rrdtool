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

    /**
     * What percentage of UNKOWN data is allowed so that the consolidated value is
     * still regarded as known: 0% - 99% (0-1). Typical is 50% (0.5).
     *
     * @var float
     */
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

    public function getRows()
    {
        return $this->rows;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function getXFilesFactor()
    {
        return $this->xFilesFactor;
    }

    public function getDataSize()
    {
        return $this->rows * static::BYTES_PER_DATAPOINT;
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
