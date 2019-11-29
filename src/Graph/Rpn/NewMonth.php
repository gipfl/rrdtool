<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Will return 1.0 whenever a step is the first of the given period (month). The
 * periods are determined according to the local timezone AND the LC_TIME
 * settings.
 *
 * CDEF:mtotal=rate,STEPWIDTH,*,NEWMONTH,0,PREV,IF,ADDNAN
 */
class NewMonth extends TimeValue
{
    const NAME = 'NEWMONTH';
}
