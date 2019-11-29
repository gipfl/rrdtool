<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Cosine (input in radians)
 */
class Cos extends ArithmeticOperator
{
    const NAME = 'COS';

    protected $requiredElements = 1;
}
