<?php

namespace gipfl\RrdTool\Graph;

use InvalidArgumentException;

class Color
{
    protected $hexCode;

    protected $alpha;

    public function __construct($hexCode, $alpha = null)
    {
        if ($hexCode === null) {
            return;
        }

        if ($hexCode instanceof Color) {
            $this->hexCode = $hexCode->getHexCode();
            $this->alpha = $hexCode->getAlphaHex();
        } else {
            $hexCode = ltrim($hexCode, '#');
            if (strlen($hexCode) === 6) {
                $this->hexCode = $hexCode;
            } elseif (strlen($hexCode) === 8) {
                $this->hexCode = substr($hexCode, 0, 6);
                $this->alpha = substr($hexCode, 6);
            } else {
                throw new InvalidArgumentException("Valid color hex code expected, got $hexCode");
            }
        }

        if ($alpha !== null) {
            $this->setAlphaHex($alpha);
        }
    }

    public function getHexCode()
    {
        return $this->hexCode;
    }

    public function setAlphaHex($alpha)
    {
        $this->alpha = $alpha;

        return $this;
    }

    public function getAlphaHex()
    {
        return $this->alpha;
    }

    public function isNull()
    {
        return $this->hexCode === null;
    }

    public function __toString()
    {
        if ($this->isNull()) {
            return '';
        }

        return '#' . $this->hexCode . (string) $this->alpha;
    }
}
