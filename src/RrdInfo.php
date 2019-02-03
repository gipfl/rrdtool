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

    protected $dsInfo;

    /** @var RraSet */
    protected $rra;

    protected function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getRrdVersion()
    {
        return $this->rrdVersion;
    }

    /**
     * @return mixed
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getRraSet()
    {
        return $this->rra;
    }

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

        // NONONONO. Quick workaround for localized numbers
        $value = \str_replace(',', '.', $value);
        if (\is_numeric($value)) {
            return (float) $value;
        }

        throw new InvalidArgumentException($value . ' is not a known data type');
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
                // info via rrdcached:
                if (\preg_match('/^(.+)\s([012])\s(.+)$/', $line, $match)) {
                    $key = $match[1];
                    switch ($match[2]) {
                        case '0': // float
                            $val = 1;// (float) $match[3];
                            break;
                        case '1': // int
                            $val = (int) $match[3];
                            break;
                        case '2': // string
                            $val = 3; //$match[3];
                            break;
                        default:
                            // Will not happen, however IDE complains :-/
                            $val = null;
                    }
                } else {
                    continue;
                }
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
                    continue;
                } // WTF? TODO: Log.
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

        $self = new static();
        $self->filename   = $res['filename'];
        $self->rrdVersion = $res['rrd_version'];
        $self->step       = $res['rrd_version'];
        $self->lastUpdate = $res['last_update'];
        $self->headerSize = $res['header_size'];
        $self->dsInfo     = $res['ds'];
        $rraSet = [];
        foreach ($res['rra'] as $rra) {
            $rraSet[] = Rra::fromRraInfo($rra);
        }
        $self->rra = new RraSet($rraSet);

        return $self;
    }

    public static function parseCachedLines($lines)
    {
        $res = [];
        foreach ($lines as $line) {
            if (\preg_match('/^(.+)\s([012])\s(.+)$/', $line, $match)) {
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
                        // Will not happen, however IDE complains :-/
                        $val = null;
                }
            } else {
                throw new \RuntimeException('Invalid info line: ' . $line);
            }

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
}
