<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * man rrdgraph_graph
 * ------------------
 * This is the same as PRINT, but printed inside the graph.
 *
 * Synopsis
 * --------
 * GPRINT:vname:format
 *
 * TODO: Check whether [:strftime|:valstrftime|:valstrfduration] is allowed,
 *       documentation doesn't mention them
 */
class GPrint extends PrintInstruction
{
    protected $tag = 'GPRINT';
}
