<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Pushes the time the currently processed value was taken at onto the stack
 */
class Time extends TimeValue
{
    const NAME = 'TIME';
}
