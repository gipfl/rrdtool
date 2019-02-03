<?php

namespace gipfl\RrdTool\GraphTemplate;

class VmwareInterfaceGraph extends InterfaceGraph
{
    protected $dsRx = 'bytesRx';

    protected $dsTx = 'bytesTx';

    // We have kBytes
    protected $multiplier = 8192;
}
