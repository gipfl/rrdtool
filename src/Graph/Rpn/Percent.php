<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop two elements (count,percent) from the stack. Now pop count element, order
 * them by size (while the smalles elements are -INF, the largest are INF and
 * NaN is larger than -INF but smaller than anything else. No pick the element
 * from the ordered list where percent of the elements are equal then the one
 * picked. Push the result back on to the stack.
 *
 * Example: CDEF:x=a,b,c,d,95,4,PERCENT
 */
class Percent extends SetOperator
{
    // TODO: we need to fetch count and one more!!
    const NAME = 'PERCENT';
}
