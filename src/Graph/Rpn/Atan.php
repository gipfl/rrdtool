<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Arctangent (output in radians)
 */
class Atan extends ArithmeticOperator
{
    const NAME = 'ATAN';

    protected $requiredElements = 1;
}
