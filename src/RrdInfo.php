<?php

namespace gipfl\RrdTool;

use gipfl\Json\JsonSerialization;
use InvalidArgumentException;
use RuntimeException;
use function ctype_digit;
use function explode;
use function is_numeric;
use function preg_match;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

// rrdtool create filename [--start|-b start time] [--step|-s step] [--template|-t template-file]
// [--source|-r source-file] [--no-overwrite|-O] [--daemon|-d address]
// [DS:ds-name[=mapped-ds-name[[source-index]]]:DST:dst arguments]
// [RRA:CF:cf arguments]
class RrdInfo implements JsonSerialization
{
    const FORMAT_RRDTOOL = 1;
    const FORMAT_RRDCACHED = 2;

    protected string $filename;
    protected int $step;
    protected DsList $dsList;
    protected RraSet $rra;
    protected ?string $rrdVersion = null; // e.g. '0003'
    protected ?int $headerSize = null;
    protected ?int $lastUpdate = null;

    public function __construct(string $filename, int $step, DsList $dsList, RraSet $rra)
    {
        $this->filename = $filename;
        $this->step = $step;
        $this->dsList = $dsList;
        $this->rra = $rra;
    }

    public function setHeaderSize(int $size)
    {
        $this->headerSize = $size;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getRrdVersion(): ?string
    {
        return $this->rrdVersion;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function getLastUpdate(): ?int
    {
        return $this->lastUpdate;
    }

    public function getDsList(): DsList
    {
        return $this->dsList;
    }

    public function listDsNames(): array
    {
        return $this->dsList->listNames();
    }

    public function getRraSet(): RraSet
    {
        return $this->rra;
    }

    public function countDataSources(): int
    {
        return count($this->dsList->listNames());
    }

    public function getHeaderSize(): ?int
    {
        return $this->headerSize;
    }

    public function getDataSize(): int
    {
        return (int) ($this->rra->getDataSize() * $this->countDataSources());
    }

    public function getMaxRetention(): int
    {
        $rra = $this->getRraSet()->getLongestRra();

        return (int) ($rra->getRows() * $rra->getSteps() * $this->getStep());
    }

    public static function parse(string $string): RrdInfo
    {
        return static::parseLines(\preg_split('/\n/', $string, -1, PREG_SPLIT_NO_EMPTY));
    }

    public static function parseLines(array $lines): RrdInfo
    {
        return static::instanceFromParsedStructure(static::prepareStructure($lines));
    }

    protected static function detectLineFormat($line): int
    {
        return false === strpos($line, ' = ')
            ? self::FORMAT_RRDCACHED
            : self::FORMAT_RRDTOOL;
    }

    /**
     * @param array $lines
     * @return array
     */
    protected static function prepareStructure(array $lines): array
    {
        $result = [];
        if (empty($lines)) {
            throw new RuntimeException('Got no info lines to parse');
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

    protected static function instanceFromParsedStructure(array $array): RrdInfo
    {
        $self = new static(
            $array['filename'],
            $array['step'],
            self::dsListFromArray($array['ds']),
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

        if (strlen($value) && $value[0] === '"') {
            return trim($value, '"');
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $value = self::eventuallyParseFloat($value);
        if ($value === false) {
            throw new InvalidArgumentException($value . ' is not a known data type');
        }

        return $value;
    }

    protected static function dsListFromArray($info): DsList
    {
        $list = new DsList();
        foreach ($info as $name => $dsInfo) {
            $list->add(DsInfo::fromArray($name, $dsInfo)->toDs());
        }

        return $list;
    }

    protected static function rraInfoFromArray($info): RraSet
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
        $value = str_replace(',', '.', $value);
        if (is_numeric($value)) {
            return (float) $value;
        } else {
            return false;
        }
    }

    protected static function splitKeyValueFromRrdTool($line): array
    {
        list($key, $val) = explode(' = ', $line);
        return [$key, self::parseValue($val)];
    }

    protected static function splitKeyValueFromRrdCached($line): array
    {
        if (preg_match('/^(.+?)\s([012])\s(.+)$/', $line, $match)) {
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
                    throw new RuntimeException("You should never reach this point");
            }
        } else {
            throw new RuntimeException("Got invalid info line from RRDcached: $line");
        }

        return [$key, $val];
    }

    protected static function setArrayValue(array &$array, $key, $value)
    {
        if (false === ($bracket = strpos($key, '['))) {
            $array[$key] = $value;
        } else {
            $type = substr($key, 0, $bracket);
            $key = substr($key, $bracket + 1);
            $bracket = strpos($key, ']');
            if ($bracket === false) {
                throw new RuntimeException('Missing right bracket in key: ' . $key);
            }
            $idx = substr($key, 0, $bracket);
            $key = substr($key, $bracket + 2);

            // No nesting support, e.g. ignore rra[0].cdp_prep[0].value
            // We also need inf/-inf support before allowing them
            if (false !== strpos($key, '[')) {
                return;
            }

            $array[$type][$idx][$key] = $value;
        }
    }

    public static function fromSerialization($any)
    {
        $self = new static(
            $any->filename,
            $any->step,
            DsList::fromSerialization($any->ds),
            RraSet::fromSerialization($any->rra)
        );

        if (isset($any->rrdVersion)) {
            $self->rrdVersion = $any->rrdVersion;
        }
        if (isset($any->lastUpdate)) {
            $self->lastUpdate = $any->lastUpdate;
        }

        return $self;
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'filename'   => $this->filename,
            'step'       => $this->step,
            'ds'         => $this->dsList,
            'rra'        => $this->rra,
            'rrdVersion' => $this->rrdVersion,
            'lastUpdate' => $this->lastUpdate,
        ];
    }
}
