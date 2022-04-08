<?php

namespace gipfl\RrdTool\RenderedGraph;

use JsonSerializable;

/**
 * graphv gives:
 * graph_left = 83
 * graph_top = 15
 * graph_width = 742
 * graph_height = 288
 * image_width = 840
 * image_height = 320
 * graph_start = 1493928095
 * graph_end = 1493942495
 * value_min = 0,0000000000e+00
 * value_max = 1,4626943333e+00
 * image = BLOB_SIZE:103461
 */
class GraphInfo implements JsonSerializable
{
    protected array $print = [];
    protected int $headerLength;
    protected int $imageSize;
    protected GraphInfoGraph $graph;
    protected GraphInfoImage $image;
    protected GraphInfoValue $value;
    protected float $timeSpent;

    /** @var string svg|png */
    protected string $format;

    /** @var string e.g. image\/svg+xml */
    protected string $type;

    /** @var string data:image\/svg+xml;utf8,%3C?xml... */
    protected string $raw;

    public function jsonSerialize(): object
    {
        return (object) [
            'print'        => $this->print,
            'headerLength' => $this->headerLength,
            'imageSize'    => $this->imageSize,
            'graph'        => $this->graph,
            'image'        => $this->image,
            'value'        => $this->value,
            'timeSpent'    => $this->timeSpent,
            'format'       => $this->format,
            'type'         => $this->type,
            'raw'          => $this->raw,
        ];
    }
}
