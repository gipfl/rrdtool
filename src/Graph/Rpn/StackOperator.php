<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Processing the stack directly
 *
 * TODO: figure out how to deal with this at parse time
 */
abstract class StackOperator extends Operator
{
    protected $requiredElements = null;
}
