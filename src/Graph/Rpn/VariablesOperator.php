<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * These operators work only on VDEF statements. Note that currently ONLY these
 * work for VDEF.
 */
abstract class VariablesOperator extends Operator
{
    protected $requiredElements = 1;
}
