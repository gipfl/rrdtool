<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the parameters for a Least Squares Line (y = mx +b) which approximate
 * the provided dataset.
 *
 * LSLSLOPE is the slope (m) of the line related to the COUNT position of the
 * data
 *
 * Example: VDEF:slope=mydata,LSLSLOPE
 */
class LeastSquaresLineSlope extends VariablesOperator
{
    const NAME = 'LSLSLOPE';
}
