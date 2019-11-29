<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the corresponding value, MAXIMUM also returns the first occurrence of
 * that value in the time component
 *
 * Example: VDEF:max=mydata,MAXIMUM
 */
class Maximum extends VariablesOperator
{
    const NAME = 'MAXIMUM';
}
