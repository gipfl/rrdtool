<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Round up to the nearest integer
 */
class Ceil extends ArithmeticOperator
{
    const NAME = 'CEIL';

    protected $requiredElements = 1;
}
