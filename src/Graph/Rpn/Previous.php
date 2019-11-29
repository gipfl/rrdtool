<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pushes an unknown value if this is the first value of a data set or
 * otherwise the result of this CDEF at the previous time step. This allows you
 * to do calculations across the data. This function cannot be used in VDEF
 * instructions.
 */
class Previous extends SpecialValue
{
    const NAME = 'PREV';
}
