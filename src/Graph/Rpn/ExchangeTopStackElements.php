<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Exchange the two top elements
 */
class ExchangeTopStackElements extends StackOperator
{
    const NAME = 'EXC';

    protected $requiredElements = 2;
}
