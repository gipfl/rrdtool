<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the first non-nan or infinite value for the selected data stream,
 * including its timestamp.
 *
 * Example: VDEF:first=mydata,FIRST
 */
class First extends VariablesOperator
{
    const NAME = 'FIRST';
}
