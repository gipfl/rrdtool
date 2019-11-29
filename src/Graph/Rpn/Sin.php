<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Sine (input in radians)
 */
class Sin extends ArithmeticOperator
{
    const NAME = 'SIN';

    protected $requiredElements = 1;
}
