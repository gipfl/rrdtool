<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Push the nth element onto the stack
 *
 *     a,b,c,d,3,INDEX -> a,b,c,d,b
 */
class Index extends StackOperator
{
    const NAME = 'INDEX';
}
