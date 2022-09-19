<?php

namespace gipfl\RrdTool\Graph\Instruction;

use function addcslashes;
use function strlen;

abstract class Instruction
{
    abstract protected function render();

    public function __toString()
    {
        return $this->render();
    }

    public static function string(?string $string): ?string
    {
        if ($string === null || strlen($string) === 0) {
            return null;
        }

        // if alnum -> just return it as is?

        // TODO: Check and fix
        return "'" . addcslashes($string, "':") . "'";
    }

    /**
     * @param ?string $parameter
     * @return string
     */
    public static function optionalParameter(?string $parameter): string
    {
        if ($parameter !== null && strlen($parameter)) {
            return ":$parameter";
        } else {
            return '';
        }
    }

    /**
     * @param string $parameter
     * @param mixed $value
     * @return string
     */
    public static function optionalNamedParameter(string $parameter, $value): string
    {
        if ($value !== null && strlen($value)) {
            return ":$parameter=$value";
        } else {
            return '';
        }
    }

    /**
     * @param string $parameter
     * @param bool|null $value
     * @return string
     */
    public static function optionalNamedBoolean(string $parameter, ?bool $value): string
    {
        if ($value) {
            return ":$parameter";
        } else {
            return '';
        }
    }
}
