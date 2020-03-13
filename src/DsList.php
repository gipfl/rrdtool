<?php

namespace gipfl\RrdTool;

class DsList
{
    protected $list = [];

    /**
     * DsList constructor.
     * @param Ds[] $list
     */
    public function __construct($list = [])
    {
        foreach ($list as $ds) {
            $this->add($ds);
        }
    }

    public function add(Ds $ds)
    {
        $this->list[$ds->getName()] = $ds;
    }

    public function listNames()
    {
        return \array_keys($this->list);
    }

    public function __toString()
    {
        $dsString = '';
        foreach ($this->list as $ds) {
            $dsString .= ' ' . $ds->toString();
        }

        return \implode(' ', $this->list);
    }
}
