<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the parameters for a Least Squares Line (y = mx +b) which approximate
 * the provided dataset.
 *
 * LSLINT is the y-intercept (b), which happens also to be the first data point
 * on the graph.
 */
class LeastSquaresLineIntercept extends VariablesOperator
{
    const NAME = 'LSLINT';
}
