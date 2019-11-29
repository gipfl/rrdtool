<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Will return 1.0 whenever a step is the first of the given period (year). The
 * periods are determined according to the local timezone AND the LC_TIME
 * settings.
 *
 * CDEF:ytotal=rate,STEPWIDTH,*,NEWYEAR,0,PREV,IF,ADDNAN
 */
class NewYear extends TimeValue
{
    const NAME = 'NEWYEAR';
}
