<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the parameters for a Least Squares Line (y = mx +b) which approximate
 * the provided dataset.
 *
 * LSLCORREL is the Correlation Coefficient (also know as Pearson's Product
 * Moment Correlation Coefficient). It will range from 0 to +/-1 and represents
 * the quality of fit for the approximation.
 */
class LeastSquaresLineCorrelationCoefficient extends VariablesOperator
{
    const NAME = 'LSLCORREL';
}
