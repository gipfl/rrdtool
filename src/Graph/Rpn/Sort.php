<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element from the stack. This is the count of items to be sorted. The
 * top count of the remaining elements are then sorted from the smallest to the
 * largest, in place on the stack.
 *
 * 4,3,22.1,1,4,SORT -> 1,3,4,22.1
 */
class Sort extends SetOperator
{
    const NAME = 'SORT';
}
