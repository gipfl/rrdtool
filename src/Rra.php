<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

abstract class Rra
{
    const BYTES_PER_DATAPOINT = 8;

    protected $consolidationFunction;

    /** @var int|null */
    protected $currentRow;

    protected function __construct($consolidationFunction)
    {
        $this->consolidationFunction = $consolidationFunction;
    }

    abstract public function toString();

    /**
     * Data Size used on disk
     *
     * @return int
     */
    abstract public function getDataSize();

    public function getConsolidationFunction()
    {
        return $this->consolidationFunction;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    /**
     * <code>
     * [
     *     'cf'          => 'AVERAGE',
     *     'rows'        => 2800,
     *     'cur_row'     => 359,
     *     'pdp_per_row' => 1,
     *     'xff'         => 0.5
     * ]
     * </code>
     *
     * TODO: What does this look like for non-compute RRAs?
     *
     * @param array $info
     * @return RraAggregation
     */
    public static function fromRraInfo(array $info)
    {
        $cf = $info['cf'];
        if (RraAggregation::isKnown($cf)) {
            $rra = new RraAggregation($cf);
            $rra->setArgumentsFromInfo($info);
        } else {
            throw new InvalidArgumentException(
                "'$cf' is not a known consolidation function"
            );
        }

        $rra->currentRow = $info['cur_row'];

        return $rra;
    }

    /**
     * 'RRA:MIN:0.5:21600:5840'
     * @param $str
     * @return Rra
     */
    public static function fromString($str)
    {
        if (\substr($str, 0, 4) !== 'RRA:') {
            throw new InvalidArgumentException(
                "An RRA must be prefixed with 'RRA:', got '$str'"
            );
        }
        $pos = \strpos($str, ':', 4);
        if ($pos === false) {
            throw new InvalidArgumentException(
                "An RRA must have the form 'RRA:CF:cf_arguments', got '$str'"
            );
        }

        $cf = \substr($str, 4, $pos - 4);
        $args = \substr($str, $pos + 1);
        if (RraAggregation::isKnown($cf)) {
            $rra = new RraAggregation($cf);
            $rra->setArgumentsFromString($args);

            return $rra;
        } else {
            throw new InvalidArgumentException(
                "'$cf' is not a known consolidation function"
            );
        }
    }
}
