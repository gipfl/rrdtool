<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;

class RrdInfo
{
    const FORMAT_RRDTOOL = 1;

    const FORMAT_RRDCACHED = 2;

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
     * @return DsInfo[]
     */
    public function getDsInfo()
    {
        return $this->dsInfo;
    }

    public function listDsNames()
    {
        return \array_keys($this->dsInfo);
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

    public function getHeaderSize()
    {
        return $this->headerSize;
    }

    /**
     * @return int
     */
    public function getDataSize()
    {
        return $this->rra->getDataSize() * $this->countDataSources();
    }

    /**
     * @param $string
     * @return static
     */
    public static function parse($string)
    {
        return static::parseLines(\preg_split('/\n/', $string, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @param array $lines
     * @return static
     */
    public static function parseLines(array $lines)
    {
        return static::instanceFromParsedStructure(static::prepareStructure(
            $lines
        ));
    }

    protected static function detectLineFormat($line)
    {
        return false === \strpos($line, ' = ')
            ? self::FORMAT_RRDCACHED
            : self::FORMAT_RRDTOOL;
    }

    /**
     * @param array $lines
     * @return array
     */
    protected static function prepareStructure(array $lines)
    {
        $result = [];
        if (empty($lines)) {
            throw new \RuntimeException('Got no info lines to parse');
        }
        $format = static::detectLineFormat($lines[0]);
        foreach ($lines as $line) {
            if ($format === self::FORMAT_RRDCACHED) {
                list($key, $value) = static::splitKeyValueFromRrdCached($line);
            } else {
                list($key, $value) = static::splitKeyValueFromRrdTool($line);
            }
            static::setArrayValue($result, $key, $value);
        }

        return $result;
    }

    protected static function instanceFromParsedStructure(array $array)
    {
        $self = new static(
            $array['filename'],
            $array['step'],
            self::dsInfoFromArray($array['ds']),
            self::rraInfoFromArray($array['rra'])
        );
        $self->rrdVersion = $array['rrd_version'];
        $self->lastUpdate = $array['last_update'];
        $self->headerSize = $array['header_size'];

        return $self;
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

    protected static function dsInfoFromArray($info)
    {
        $result = [];
        foreach ($info as $name => $dsInfo) {
            $result[$name] = DsInfo::fromArray($name, $dsInfo);
        }

        return $result;
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

    protected static function splitKeyValueFromRrdTool($line)
    {
        list($key, $val) = \explode(' = ', $line);
        return [$key, self::parseValue($val)];
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

    protected static function setArrayValue(array &$array, $key, $value)
    {
        if (false === ($bracket = \strpos($key, '['))) {
            $array[$key] = $value;
        } else {
            $type = \substr($key, 0, $bracket);
            $key = \substr($key, $bracket + 1);
            $bracket = \strpos($key, ']');
            if ($bracket === false) {
                throw new \RuntimeException('Missing right bracket in key: ' . $key);
            }
            $idx = \substr($key, 0, $bracket);
            $key = \substr($key, $bracket + 2);

            // No nesting support, e.g. ignore rra[0].cdp_prep[0].value
            // We also need inf/-inf support before allowing them
            if (false !== \strpos($key, '[')) {
                return;
            }

            $array[$type][$idx][$key] = $value;
        }
    }
}
