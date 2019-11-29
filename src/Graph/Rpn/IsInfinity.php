<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element from the stack, compare this to positive or negative
 * infinity.
 *
 * Returns 1 for true or 0 for false.
 */
class IsInfinity extends BooleanOperator
{
    const NAME = 'ISINF';

    protected $requiredElements = 1;
}
