<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pop one element (count) from the stack. Now pop count elements and push the
 * minimum back onto the stack.
 *
 * Example: CDEF:x=a,b,c,d,4,SMIN
 */
class SetMin extends SetOperator
{
    const NAME = 'SMIN';
}
