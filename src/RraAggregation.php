<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

class RraAggregation extends Rra
{
    protected static array $functions = [
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
    protected float $xFilesFactor;

    protected int $steps;

    public static function isKnown($name)
    {
        return \in_array($name, self::$functions);
    }

    /**
     * @param string $str xff:steps:rows
     */
    public function setArgumentsFromString(string $str)
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

    public function getSteps(): int
    {
        return $this->steps;
    }

    public function getXFilesFactor(): ?float
    {
        return $this->xFilesFactor;
    }

    public function getDataSize(): int
    {
        return (int) ($this->rows * static::BYTES_PER_DATAPOINT);
    }

    public function toString(): string
    {
        return 'RRA:' . \implode(':', [
            $this->consolidationFunction,
            $this->xFilesFactor,
            $this->steps,
            $this->rows
        ]);
    }
}
