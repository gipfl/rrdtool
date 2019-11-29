<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element from the stack, compare this to unknown.
 *
 * Returns 1 for true or 0 for false.
 */
class IsUnknown extends BooleanOperator
{
    const NAME = 'UN';

    protected $requiredElements = 1;
}
