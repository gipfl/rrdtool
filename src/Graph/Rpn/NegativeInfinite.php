<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pushes a negative infinite value on the stack. When such a value is graphed,
 * it appears at the top or bottom of the graph, no matter what the actual
 * value on the y-axis is.
 */
class NegativeInfinite extends SpecialValue
{
    const NAME = 'NEGINF';
}
