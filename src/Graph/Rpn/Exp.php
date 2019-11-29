<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Exp (natural logarithm)
 */
class Exp extends ArithmeticOperator
{
    const NAME = 'EXP';

    protected $requiredElements = 1;
}
