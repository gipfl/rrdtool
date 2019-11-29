<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Returns the standard deviation of the values
 *
 * Example: VDEF:stdev=mydata,STDEV
 */
class StreamStandardDeviation extends VariablesOperator
{
    // TODO: teach the parser how to distinct this from 'normal' STDEV
    const NAME = 'STDEV';
}
