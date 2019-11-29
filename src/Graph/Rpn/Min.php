<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pops two elements from the stack and returns the smaller
 *
 * Note that infinite is larger than anything else. If one of the input numbers
 * is unknown then the result of the operation will be unknown too.
 */
class Min extends CompareValuesOperator
{
    const NAME = 'MIN';
}
