<?php

namespace gipfl\RrdTool\Graph;

/**
 * Synopsis
 *
 * PRINT:vname:format[:strftime|:valstrftime|:valstrfduration]
 */
class PrintInstruction extends Instruction
{
    /** @var string */
    protected $variableName;

    /** @var string */
    protected $format;

    // TODO: [:strftime|:valstrftime|:valstrfduration]
    public function __construct($vname, $format)
    {
        $this->setVariableName($vname);
        $this->setFormat($format);
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @param string $variableName
     * @return $this
     */
    public function setVariableName($variableName)
    {
        $this->variableName = $variableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return PrintInstruction
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    public function render()
    {
        return 'PRINT:' . $this->getVariableName() . $this->getFormat();
    }
}
