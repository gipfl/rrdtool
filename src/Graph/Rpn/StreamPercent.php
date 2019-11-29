<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * This should follow a DEF or CDEF vname. The vname is popped, another number
 * is popped which is a certain percentage (0..100). The data set is then
 * sorted and the value returned is chosen such that percentage percent of the
 * values is lower or equal than the result.
 *
 * For PERCENTNAN Unknown values are ignored, but for PERCENT Unknown values
 * are considered lower than any finite number for this purpose so if this
 * operator returns an unknown you have quite a lot of them in your data.
 *
 * Infinite numbers are lesser, or more, than the finite numbers and are always
 * more than the Unknown numbers. (NaN < -INF < finite values < INF)
 *
 * Example: VDEF:perc95=mydata,95,PERCENT VDEF:percnan95=mydata,95,PERCENTNAN
 */
class StreamPercent extends VariablesOperator
{
    // TODO: teach the parser how to distinct this from 'normal' PERCENT
    const NAME = 'PERCENT';
}
