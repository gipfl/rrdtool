<?php

namespace gipfl\RrdTool\Graph;

abstract class Instruction
{
    abstract protected function render();

    public function __toString()
    {
        return $this->render();
    }

    protected function string($string)
    {
        if ($string === null || strlen($string) === 0) {
            return null;
        }

        // TODO: Check and fix
        return "'" . addcslashes($string, "':") . "'";
    }

    /**
     * @param $parameter
     * @return string
     */
    protected function optionalParameter($parameter)
    {
        if (strlen($parameter)) {
            return ":$parameter";
        } else {
            return '';
        }
    }

    /**
     * @param $parameter
     * @return string
     */
    protected function optionalNamedParameter($parameter, $value)
    {
        if (strlen($value)) {
            return ":${parameter}=${value}";
        } else {
            return '';
        }
    }

    /**
     * @param $color
     * @return Color
     */
    protected function wantColor($color)
    {
        if ($color instanceof Color) {
            return clone($color);
        } else {
            return new Color($color);
        }
    }
}
