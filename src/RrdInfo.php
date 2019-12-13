<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

class RrdInfo
{
    /** @var string */
    protected $filename;

    /** @var string */
    protected $rrdVersion;

    /** @var int */
    protected $headerSize;

    /** @var int */
    protected $step;

    /** @var int|null */
    protected $lastUpdate;

    /** @var DsInfo[] */
    protected $dsInfo;

    /** @var RraSet */
    protected $rra;

    protected function __construct($filename, $step, array $dsInfo, RraSet $rra)
    {
        $this->filename = $filename;
        $this->step = $step;
        $this->dsInfo = $dsInfo;
        $this->rra = $rra;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getRrdVersion()
    {
        return $this->rrdVersion;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return int|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @return mixed
     */
    public function getDsInfo()
    {
        return $this->dsInfo;
    }

    /**
     * @return RraSet
     */
    public function getRraSet()
    {
        return $this->rra;
    }

    /**
     * @return int
     */
    public function countDataSources()
    {
        return \count($this->dsInfo);
    }

    /**
     * @return int
     */
    public function getDataSize()
    {
        return $this->rra->getDataSize() * $this->countDataSources();
    }

    /**
     * @param $value
     * @return bool|float|int|string|null
     */
    protected static function parseValue($value)
    {
        if ($value === 'NaN') {
            return null;
        }

        if (\strlen($value) && $value[0] === '"') {
            return \trim($value, '"');
        }

        if (\ctype_digit($value)) {
            return (int) $value;
        }

        $value = self::eventuallyParseFloat($value);
        if ($value === false) {
            throw new InvalidArgumentException($value . ' is not a known data type');
        }

        return $value;
    }

    public static function parseRrdToolOutput($info)
    {
        return static::parseLines(\preg_split('/\n/', $info, -1, PREG_SPLIT_NO_EMPTY));
    }

    public static function parseLines($lines)
    {
        $res = [];
        foreach ($lines as $line) {
            // rrdtool info:
            if (false === strpos($line, ' = ')) {
                // info via rrdcached
                list($key, $val) = static::splitKeyValueFromRrdCached($line);
            } else {
                list($key, $val) = \explode(' = ', $line);
            }

            if (false === ($bracket = \strpos($key, '['))) {
                $res[$key] = self::parseValue($val);
            } else {
                $type = \substr($key, 0, $bracket);
                $key = \substr($key, $bracket + 1);
                $bracket = \strpos($key, ']');
                if ($bracket === false) {
                    throw new \RuntimeException('Missing right bracket: ' . $line);
                }
                $idx = \substr($key, 0, $bracket);
                $key = \substr($key, $bracket + 2);

                // No nesting support, e.g. ignore rra[0].cdp_prep[0].value
                // We also need inf/-inf support before allowing them
                if (false !== \strpos($key, '[')) {
                    continue;
                }

                $res[$type][$idx][$key] = self::parseValue($val);
            }
        }

        $self = new static(
            $res['filename'],
            $res['step'],
            self::dsInfoFromArray($res['ds']),
            self::rraInfoFromArray($res['rra'])
        );
        $self->rrdVersion = $res['rrd_version'];
        $self->lastUpdate = $res['last_update'];
        $self->headerSize = $res['header_size'];

        return $self;
    }

    public static function parseCachedLines($lines)
    {
        $res = [];
        foreach ($lines as $line) {
            list($key, $val) = static::splitKeyValueFromRrdCached($line);

            if (false === ($bracket = \strpos($key, '['))) {
                $res[$key] = $val;
            } else {
                $type = \substr($key, 0, $bracket);
                $key = \substr($key, $bracket + 1);
                $bracket = \strpos($key, ']');
                if ($bracket === false) {
                    throw new \RuntimeException('Missing right bracket: ' . $line);
                }
                $idx = \substr($key, 0, $bracket);
                $key = \substr($key, $bracket + 2);

                // No nesting support, e.g. ignore rra[0].cdp_prep[0].value
                // We also need inf/-inf support before allowing them
                if (false !== \strpos($key, '[')) {
                    continue;
                }

                $res[$type][$idx][$key] = $val; // self::parseValue($val);
            }
        }

        return $res;
    }

    protected static function dsInfoFromArray($info)
    {
        $dsInfo = [];
        foreach ($info as $name => $dsInfo) {
            $dsInfo[$name] = DsInfo::fromArray($name, $dsInfo);
        }

        return $dsInfo;
    }

    protected static function rraInfoFromArray($info)
    {
        $rraSet = [];
        foreach ($info as $rra) {
            $rraSet[] = Rra::fromRraInfo($rra);
        }

        return new RraSet($rraSet);
    }

    protected static function eventuallyParseFloat($value)
    {
        // dirty workaround for localized numbers
        $value = \str_replace(',', '.', $value);
        if (\is_numeric($value)) {
            return (float) $value;
        } else {
            return false;
        }
    }

    protected static function splitKeyValueFromRrdCached($line)
    {
        if (\preg_match('/^(.+?)\s([012])\s(.+)$/', $line, $match)) {
            $key = $match[1];
            switch ($match[2]) {
                case '0': // float
                    if ($match[3] === 'NaN') {
                        $val = null;
                    } else {
                        $val = (float) $match[3];
                    }
                    break;
                case '1': // int
                    if ($match[3] === 'NaN') {
                        $val = null;
                    } else {
                        $val = (int) $match[3];
                    }
                    break;
                case '2': // string
                    $val = $match[3];
                    break;
                default:
                    // This is impossible because of the above regex, but makes our IDE happy:
                    throw new \RuntimeException("You should never reach this point");
            }
        } else {
            throw new \RuntimeException("Got invalid info line from RRDcached: $line");
        }

        return [$key, $val];
    }
}
