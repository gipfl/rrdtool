<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pushes the number 1 if this is the first value of the data set, the number
 * 2 if it is the second, and so on. This special value allows you to make
 * calculations based on the position of the value within the data set. This
 * function cannot be used in VDEF instructions.
 */
class Count extends SpecialValue
{
    const NAME = 'COUNT';
}
