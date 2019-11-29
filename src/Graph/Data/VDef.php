<?php

namespace gipfl\RrdTool\Graph\Data;

/**
 * Calculate an aggregated single value
 *
 * man rrdgraph_data
 * -----------------
 * This command returns a value and/or a time according to the RPN statements
 * used. The resulting vname will, depending on the functions used, have a
 * value and a time component. When you use this vname in another RPN expression,
 * you are effectively inserting its value just as if you had put a number at
 * that place. The variable can also be used in the various graph and print
 * elements.
 *
 * Example: VDEF:avg=mydata,AVERAGE
 *
 * Note that currently only aggregation functions work in VDEF rpn expressions.
 * Patches to change this are welcome.
 *
 * Synopsis
 * --------
 * VDEF:vname=RPN expression
 */
class VDef extends Expression
{
    protected $tag = 'VDEF';
}
