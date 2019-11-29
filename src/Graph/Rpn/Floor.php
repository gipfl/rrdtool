<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Round down to the nearest integer
 */
class Floor extends ArithmeticOperator
{
    const NAME = 'FLOOR';

    protected $requiredElements = 1;
}
