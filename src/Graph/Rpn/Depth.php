<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pushes the current depth of the stack onto the stack
 *
 *     a,b,DEPTH -> a,b,2
 */
class Depth extends StackOperator
{
    const NAME = 'DEPTH';

    protected $requiredElements = 0;
}
