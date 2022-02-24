<?php

namespace gipfl\RrdTool\Graph\Data;

/**
 * Synopsis:
 *
 * DEF:<vname>=<rrdfile>:<ds-name>:<CF>[:step=<step>][:start=<time>][:end=<time>]
 *    [:reduce=<CF>][:daemon=<address>]
 */
class Def extends Definition
{
    /** @var string */
    protected $rrdFile;

    /** @var string */
    protected $dsName;

    /** @var string */
    protected $consolidationFunction;

    /** @var int|null */
    protected $step;

    /** @var string|null */
    protected $start;

    /** @var string|null */
    protected $end;

    /** @var string|null */
    protected $reduce;

    /** @var string|null */
    protected $daemon;

    public function __construct($variableName, $rrdFile, $consolidationFunction)
    {
        $this->setVariableName($variableName);
        $this->setRrdFile($rrdFile);
        $this->setConsolidationFunction($consolidationFunction);
    }

    /**
     * @return string
     */
    public function getRrdFile()
    {
        return $this->rrdFile;
    }

    /**
     * @param string $rrdFile
     * @return $this
     */
    public function setRrdFile($rrdFile)
    {
        $this->rrdFile = $rrdFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getDsName()
    {
        return $this->dsName;
    }

    /**
     * @param string $dsName
     * @return $this
     */
    public function setDsName($dsName)
    {
        $this->dsName = $dsName;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsolidationFunction()
    {
        return $this->consolidationFunction;
    }

    /**
     * @param string $consolidationFunction
     * @return $this
     */
    public function setConsolidationFunction($consolidationFunction)
    {
        $this->consolidationFunction = $consolidationFunction;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param int|null $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param string|null $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param string|null $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReduce()
    {
        return $this->reduce;
    }

    /**
     * @param string|null $reduce
     * @return $this
     */
    public function setReduce($reduce)
    {
        $this->reduce = $reduce;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDaemon()
    {
        return $this->daemon;
    }

    /**
     * @param string|null $daemon
     * @return $this
     */
    public function setDaemon($daemon)
    {
        $this->daemon = $daemon;
        return $this;
    }

    public static function parseExpression($string)
    {
    }

    protected function render()
    {
        return \sprintf(
            'DEF:%s=%s:%s:%s',
            $this->getVariableName(),
            $this->getRrdFile(), // TODO: ESCAPE!!
            $this->getDsName(),
            $this->getConsolidationFunction()
        )
        . $this::optionalNamedParameter('step', $this->getStep())
        . $this::optionalNamedParameter('start', $this->getStart())
        . $this::optionalNamedParameter('end', $this->getEnd())
        . $this::optionalNamedParameter('reduce', $this->getReduce())
        . $this::optionalNamedParameter('daemon', $this->getDaemon())
        ;
    }
}
