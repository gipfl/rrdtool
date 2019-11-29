<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * NAN-safe version of MAX.
 *
 * If one of the input numbers is unknown then the result of the operation will
 * be the other one. If both are unknown, then the result of the operation is
 * unknown.
 */
class MaxNan extends CompareValuesOperator
{
    const NAME = 'MAXNAN';
}
