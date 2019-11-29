<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Convert angle in radians to degrees
 */
class Rad2Deg extends ArithmeticOperator
{
    const NAME = 'RAD2DEG';

    protected $requiredElements = 1;
}
