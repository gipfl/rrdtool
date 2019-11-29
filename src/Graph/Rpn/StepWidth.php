<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * The width of the current step in seconds. You can use this to go back from
 * rate based presentations to absolute numbers
 *
 * CDEF:abs=rate,STEPWIDTH,*,PREV,ADDNAN
 */
class StepWidth extends TimeValue
{
    const NAME = 'STEPWIDTH';
}
