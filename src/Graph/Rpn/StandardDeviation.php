<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element (count) from the stack. Now pop count elements and calculate
 * the standard deviation over these values (ignoring any NAN values). Push the
 * result back on to the stack.
 *
 * Example: CDEF:x=a,b,c,d,4,STDEV
 */
class StandardDeviation extends SetOperator
{
    const NAME = 'STDEV';
}
