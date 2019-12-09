<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

class RraForecasting extends Rra
{
    protected static $functions = [
        'HWPREDICT',
        'MHWPREDICT',
        'SEASONAL',
        'DEVSEASONAL',
        'DEVPREDICT',
        'FAILURES',
    ];

    protected $rows;

    protected $alpha;

    protected $beta;

    protected $gamma;

    protected $seasonalPeriod;

    protected $rraNum;

    protected $smoothingWindow;

    protected $threshold;

    protected $windowLength;

    public static function isKnown($name)
    {
        return \in_array($name, self::$functions);
    }

    protected function parseNamedArgument($str)
    {
        $pos = \strpos($str, '=');
        if ($pos === false) {
            throw new InvalidArgumentException(
                "Expected 'key=value', like 'smoothing-window=fraction' - got '$str''"
            );
        }

        $key = \substr($str, 0, $pos);
        $val = \substr($str, $pos + 1);
        switch ($key) {
            case 'smoothing-window':
                $this->smoothingWindow = $val;
                break;
            default:
                throw new InvalidArgumentException(
                    "Got unknown named argument '$key'"
                );
        }
    }

    public function toString()
    {
        switch ($this->consolidationFunction) {
            case 'HWPREDICT':
            case 'MHWPREDICT':
                $result = \implode(':', [
                    $this->rows,
                    $this->alpha,
                    $this->beta,
                    $this->seasonalPeriod
                ]);
                if ($this->rraNum !== null) {
                    $result .= ':' . $this->rraNum;
                }
                break;
            case 'SEASONAL':
            case 'DEVSEASONAL':
                $result = \implode(':', [
                    $this->seasonalPeriod,
                    $this->gamma,
                    $this->rraNum
                ]);
                if ($this->smoothingWindow !== null) {
                    $result .= ':smoothing-window=' . $this->smoothingWindow;
                }
                break;
            case 'DEVPREDICT':
                $result = \implode(':', [
                    $this->rows,
                    $this->rraNum
                ]);
                break;
            case 'FAILURES':
                $result = \implode(':', [
                    $this->rows,
                    $this->threshold,
                    $this->windowLength,
                    $this->rraNum
                ]);
                break;
        }

        return $result;
    }

    /**
     * TODO: Check whether this really applies to forecasting RRAs
     *
     * @return int
     */
    public function getDataSize()
    {
        return $this->rows * static::BYTES_PER_DATAPOINT;
    }

    /**
     * xff:steps:rows
     * @param $str
     */
    public function setArgumentsFromString($str)
    {
        $parts = \preg_split('/:/', $str);
        $cntParts = \count($parts);


        switch ($this->consolidationFunction) {
            case 'HWPREDICT':
            case 'MHWPREDICT':
                if ($cntParts < 4 || $cntParts > 5) {
                    throw new InvalidArgumentException(
                        "Expected 'rows:alpha:beta:seasonal period[:rra-num]', got '$str'"
                    );
                }
                $this->rows = $parts[0];
                $this->alpha = $parts[1];
                $this->beta = $parts[2];
                $this->seasonalPeriod = $parts[3];
                if (isset($parts[4]) && \strlen($parts[4])) {
                    $this->rraNum = $parts[4];
                }
                break;
            case 'SEASONAL':
            case 'DEVSEASONAL':
                if ($cntParts < 3 || $cntParts > 4) {
                    throw new InvalidArgumentException(
                        "Expected 'seasonal period:gamma:rra-num[:smoothing-window=fraction]', got '$str'"
                    );
                }
                $this->seasonalPeriod = $parts[0];
                $this->gamma = $parts[1];
                $this->rraNum = $parts[2];
                if (isset($parts[3]) && \strlen($parts[3])) {
                    $this->parseNamedArgument($parts[3]);
                }
                break;
            case 'DEVPREDICT':
                if ($cntParts !== 2) {
                    throw new InvalidArgumentException(
                        "Expected 'rows:rra-num', got '$str'"
                    );
                }
                $this->rows = $parts[0];
                $this->rraNum = $parts[1];
                break;
            case 'FAILURES':
                if ($cntParts !== 4) {
                    throw new InvalidArgumentException(
                        "Expected 'rows:threshold:window length:rra-num', got '$str'"
                    );
                }

                $this->rows = $parts[0];
                $this->threshold = $parts[1];
                $this->windowLength = $parts[2];
                $this->rraNum = $parts[3];
                break;
        }
    }
}
