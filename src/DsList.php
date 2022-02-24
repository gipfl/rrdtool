<?php

namespace gipfl\RrdTool;

use RuntimeException;
use function implode;

class DsList
{
    /** @var Ds[] */
    protected array $list = [];

    /**
     * @param Ds[] $list
     */
    public function __construct(array $list = [])
    {
        foreach ($list as $ds) {
            $this->add($ds);
        }
    }

    public function add(Ds $ds)
    {
        $this->list[$ds->getName()] = $ds;
    }

    public static function fromString($str): DsList
    {
        $self = new static();
        foreach (\preg_split('/ /', $str) as $ds) {
            $self->add(Ds::fromString($ds));
        }

        return $self;
    }

    /**
     * @return Ds[]
     */
    public function getDataSources(): array
    {
        return $this->list;
    }

    public function listNames(): array
    {
        return \array_keys($this->list);
    }

    public function applyAliasesMap(array $map)
    {
        foreach ($map as $alias => $name) {
            if (isset($this->list[$name])) {
                $this->list[$name]->setAlias($alias);
            } else {
                throw new RuntimeException('There is no "%s" in this DsList: ' . $this, $name);
            }
        }
    }

    public function getAliasesMap(): array
    {
        $map = [];
        foreach ($this->list as $ds) {
            $map[$ds->getAlias()] = $ds->getName();
        }

        return $map;
    }

    public function __toString(): string
    {
        $dsString = '';
        foreach ($this->list as $ds) {
            $dsString .= ' ' . $ds->toString();
        }

        return implode(' ', $this->list);
    }
}
