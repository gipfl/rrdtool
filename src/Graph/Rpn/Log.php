<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Log (natural logarithm)
 */
class Log extends ArithmeticOperator
{
    const NAME = 'LOG';

    protected $requiredElements = 1;
}
