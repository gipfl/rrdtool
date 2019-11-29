<?php

namespace gipfl\RrdTool\Graph\Instruction;

abstract class Instruction
{
    abstract protected function render();

    public function __toString()
    {
        return $this->render();
    }

    public static function string($string)
    {
        if ($string === null || \strlen($string) === 0) {
            return null;
        }

        // if alnum -> just return it as is?

        // TODO: Check and fix
        return "'" . \addcslashes($string, "':") . "'";
    }

    /**
     * @param $parameter
     * @return string
     */
    public static function optionalParameter($parameter)
    {
        if (\strlen($parameter)) {
            return ":$parameter";
        } else {
            return '';
        }
    }

    /**
     * @param $parameter
     * @return string
     */
    public static function optionalNamedParameter($parameter, $value)
    {
        if (\strlen($value)) {
            return ":${parameter}=${value}";
        } else {
            return '';
        }
    }
}
