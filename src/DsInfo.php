<?php

namespace gipfl\RrdTool;

class DsInfo
{
    public $name;

    public $index;

    public $type;

    public $minimalHeartbeat;

    public $min;

    public $max;

    public $lastDs;

    public $value;

    public $unknownSec;

    protected function __construct($name)
    {
        $this->name = $name;
    }

    public static function fromArray($name, $values)
    {
        $info = new static($name);
        foreach ($values as $arrayKey => $value) {
            switch ($arrayKey) {
                case 'index':
                case 'type':
                case 'min':
                case 'value':
                    $info->$arrayKey = $value;
                    break;
                case 'minimal_heartbeat':
                    $info->minimalHeartbeat = $value;
                    break;
                case 'last_ds':
                    $info->lastDs = $value;
                    break;
                case 'unknown_sec':
                    $info->unknownSec = $value;
                    break;
                default:
                    // Ignore unknown properties
                    // TODO: set whatever we need
            }
        }

        return $info;
    }

    public function toDs()
    {
        return new Ds(
            $this->name,
            $this->type,
            $this->minimalHeartbeat,
            $this->min,
            $this->max
        );
    }
}
