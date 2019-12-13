<?php

namespace gipfl\RrdTool;

class Ds
{
    /** @var string ds-name must be 1 to 19 characters long, allowed chars: [a-zA-Z0-9_] */
    protected $name;

    /** @var int */
    protected $heartbeat;

    // COUNTER|GAUGE|ABSOLUTE|DERIVE|DCOUNTER|DDERIVE - and COMPUTE
    /** @var string */
    protected $type;

    /** @var int */
    protected $min;

    /** @var int */
    protected $max;

    public function __construct($name, $type, $heartbeat, $min = null, $max = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->heartbeat = $heartbeat;
        $this->min  = $min;
        $this->max  = $max;
    }

    public static function create($name, $type, $heartbeat, $min = null, $max = null)
    {
        return new static($name, $type, $heartbeat, $min, $max);
    }

    public static function createWithPnpDefault($name, $type)
    {
        return new static($name, $type, 8640);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getHeartbeat()
    {
        return $this->heartbeat;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    public function toString()
    {
        // U -> unknown
        $min = $this->min === null ? 'U' : $this->min;
        $max = $this->max === null ? 'U' : $this->max;
        $dsParams = "$min:$max";

        return sprintf(
            "DS:%s:%s:%d:%s",
            $this->name,
            $this->type,
            $this->heartbeat,
            $dsParams
        );
    }
}
