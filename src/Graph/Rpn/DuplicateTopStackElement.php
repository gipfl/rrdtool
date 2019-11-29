<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Duplicate the top element
 */
class DuplicateTopStackElement extends StackOperator
{
    const NAME = 'DUP';

    protected $requiredElements = 1;
}
