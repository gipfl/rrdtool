<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Square root
 */
class Sqrt extends ArithmeticOperator
{
    const NAME = 'SQRT';

    protected $requiredElements = 1;
}
