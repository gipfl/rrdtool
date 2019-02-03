<?php

namespace gipfl\RrdTool\Graph;

trait Dashes
{
    /** @var string|null */
    protected $dashes;

    /** @var string|null */
    protected $dashOffset;

    /**
     * @return string|null
     */
    public function getDashes()
    {
        return $this->dashes;
    }

    /**
     * @param string|null $dashes
     * @return $this
     */
    public function setDashes($dashes)
    {
        $this->dashes = $dashes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDashOffset()
    {
        return $this->dashOffset;
    }

    /**
     * @param string|null $dashOffset
     * @return $this
     */
    public function setDashOffset($dashOffset)
    {
        $this->dashOffset = $dashOffset;
        return $this;
    }

    /**
     * @return string
     */
    protected function renderDashProperties()
    {
        /** @var Instruction $self */
        $self = $this;
        return $self->optionalNamedParameter('dashes', $this->getDashes())
            . $self->optionalNamedParameter('dash-offset', $this->getDashOffset());
    }
}
