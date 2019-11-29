<?php

namespace gipfl\RrdTool\Graph\Data;

/**
 * Synopsis:
 * CDEF:vname=RPN expression
 */
class CDef extends Expression
{
    protected $tag = 'CDEF';
}
