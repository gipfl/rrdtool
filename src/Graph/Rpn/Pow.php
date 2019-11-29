<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * value,power,POW
 *
 * Raise value to the power of power.
 */
class Pow extends ArithmeticOperator
{
    const NAME = 'POW';

    protected $requiredElements = 2;
}
