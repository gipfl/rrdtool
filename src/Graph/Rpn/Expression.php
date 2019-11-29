<?php

namespace gipfl\RrdTool\Graph\Rpn;

class Expression
{
    const KNOWN_OPERATORS = [

    ];

    /**
     * Expression constructor.
     * @param Operator $operator
     * @param mixed ...$parameters
     */
    public function __construct(Operator $operator, ...$parameters)
    {
    }

    public static function parse($string)
    {

    }
}
