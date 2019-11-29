<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * VRULE:time#color[:[legend][:dashes[=on_s[,off_s[,on_s,off_s]...]][:dash-offset=offset]]]
 */
class VRule extends HRule
{
    protected $tag = 'VRULE';
}
