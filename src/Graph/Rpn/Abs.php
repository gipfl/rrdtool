<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Take the absolute value
 */
class Abs extends ArithmeticOperator
{
    const NAME = 'ABS';

    protected $requiredElements = 1;
}
