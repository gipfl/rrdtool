<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element (count) from the stack. Now pop count elements and find the
 * median, ignoring all UNKNOWN values in the process. If there are an even
 * number of non-UNKNOWN values, the average of the middle two will be pushed
 * on the stack.
 *
 * Example: CDEF:x=a,b,c,d,4,MEDIAN
 */
class Median extends SetOperator
{
    const NAME = 'MEDIAN';
}
