<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pops two elements from the stack and uses them to define a range. Then it
 * pops another element and if it falls inside the range, it is pushed back.
 * If not, an unknown is pushed.
 *
 * The range defined includes the two boundaries (so: a number equal to one of
 * the boundaries will be pushed back). If any of the three numbers involved is
 * either unknown or infinite this function will always return an unknown
 *
 * Example: CDEF:a=alpha,0,100,LIMIT will return unknown if alpha is lower than
 * 0 or if it is higher than 100.
 */
class Limit extends CompareValuesOperator
{
    const NAME = 'LIMIT';

    protected $requiredElements = 3;
}
