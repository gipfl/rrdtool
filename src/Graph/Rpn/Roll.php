<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * rotate the top n elements of the stack by m
 *
 * n,m,ROLL
 *
 *     a,b,c,d,3,1,ROLL  => a,d,b,c
 *     a,b,c,d,3,-1,ROLL => a,c,d,b
 *
 * TODO: we need two PLUS n values
 */
class Roll extends StackOperator
{
    const NAME = 'ROLL';
}
