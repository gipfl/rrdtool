<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Will return 1.0 whenever a step is the first of the given period (day). The
 * periods are determined according to the local timezone AND the LC_TIME
 * settings.
 *
 * CDEF:dtotal=rate,STEPWIDTH,*,NEWDAY,0,PREV,IF,ADDNAN
 */
class NewDay extends TimeValue
{
    const NAME = 'NEWDAY';
}
