<?php

namespace gipfl\RrdTool;

use InvalidArgumentException;
use RuntimeException;

class RrdGraphInfo
{
    /** @var RrdGraph */
    protected $graph;

    public function __construct(RrdGraph $graph)
    {
        $this->graph = $graph;
    }

    public function getDetails(Rrdtool $rrdtool)
    {
        // This is what we're going to parse:
        /*
        graph_left = 39
        graph_top = 15
        graph_width = 1546
        graph_height = 680
        image_width = 1600
        image_height = 400
        graph_start = 1501538400
        graph_end = 1533564000
        value_min = 0,0000000000e+00
        value_max = 1,0647666667e+01
        image = BLOB_SIZE:1229123
         */

        $image = $this->graph->getRaw($rrdtool, true);

        $props = [];
        $pos = 0;
        $blobSize = null;
        while ($blobSize === null) {
            $newLine = strpos($image, "\n", $pos);
            $line = substr($image, $pos, $newLine - $pos);
            $pos = $newLine + 1;
            if (preg_match('/^([a-z_]+)\s=\s(.+)$/', $line, $match)) {
                $key = $match[1];
                if ($key === 'image') {
                    // image = BLOB_SIZE:1229123
                    $blobSize = (int)preg_replace('/^BLOB_SIZE:/', '', $match[2]);
                    break;
                } else {
                    list($ns, $relKey) = preg_split('/_/', $key, 2);
                }
                switch ($ns) {
                    case 'graph':
                    case 'image':
                        $value = (int)$match[2];
                        break;
                    case 'value':
                        // Hint: value is localized
                        $value = (float)str_replace(',', '.', $match[2]);
                        break;
                    default:
                        $value = (string)$match[2];
                }
                $props[$ns][$relKey] = $value;
            } elseif (preg_match('/^print\[(\d+)\]\s=\s(.+)$/', $line, $match)) {
                $key = $this->graph->getPrintLabel($match[1]);
                $value = $match[2];
                if (preg_match('/^"(\d+[,.]\d+)"$/', $value, $match)) {
                    $value = (float) str_replace(',', '.', $match[1]);
                }
                $props['print'][$key] = $value;
            } else {
                throw new RuntimeException("Unable to parse rrdgraph info line: $line");
            }
        }
        $format = $this->graph->getFormat();
        $image = substr($image, $pos);
        $contentType = $this->getContentType();
        $props['raw'] = "data:$contentType;base64," . base64_encode($image);
        $props['format'] = strtolower($format);
        $props['type'] = $contentType;

        return $props;
    }

    protected function getContentType()
    {
        switch ($this->graph->getFormat()) {
            case 'SVG':
                return 'image/svg+xml';
            case 'PNG':
                return 'image/png';
            default:
                throw new InvalidArgumentException(sprintf(
                    'RrdGraph format %s is not supported',
                    $this->graph->getFormat()
                ));
        }
    }
}
