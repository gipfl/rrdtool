<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * Draw a vertical line
 *
 * man rrdgraph_graph
 * ------------------
 * Draw a vertical line at time. Its color is composed from three hexadecimal
 * numbers specifying the rgb color components (00 is off, FF is maximum) red,
 * green and blue followed by an optional alpha.
 *
 * Optionally, a legend box and string is printed in the legend section. time
 * may be a number or a variable from a VDEF. It is an error to use vnames from
 * DEF or CDEF here. Dashed lines can be drawn using the dashes modifier.
 *
 * See LINE for more details.
 *
 * Synopsis
 * --------
 * VRULE:time#color[:[legend][:dashes[=on_s[,off_s[,on_s,off_s]...]][:dash-offset=offset]]]
 */
class VRule extends HRule
{
    protected $tag = 'VRULE';
}
