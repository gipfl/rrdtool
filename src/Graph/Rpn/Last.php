<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Return the last non-nan or infinite value for the selected data stream,
 * including its timestamp.
 *
 * Example: VDEF:last=mydata,LAST
 */
class Last extends VariablesOperator
{
    const NAME = 'LAST';
}
