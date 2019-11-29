<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Returns the rate from each defined time slot multiplied with the step size.
 * This can, for instance, return total bytes transferred when you have logged
 * bytes per second. The time component returns the number of seconds.
 *
 * Example: VDEF:total=mydata,TOTAL
 */
class Total extends VariablesOperator
{
    const NAME = 'TOTAL';
}
