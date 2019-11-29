<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Convert angle in degrees to radians
 */
class Deg2Rad extends ArithmeticOperator
{
    const NAME = 'DEG2RAD';

    protected $requiredElements = 1;
}
