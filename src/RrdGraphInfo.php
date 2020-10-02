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
        $image = $this->graph->getRaw($rrdtool, true);
        $props = $this->parseRawImage($image);
        $props['print'] = $this->graph->translatePrintLabels($props['print']);
        $imageString = \substr($image, $props['headerLength']);
        static::appendImageToProps($props, $imageString, $this->graph->getFormat());

        return $props;
    }

    public static function appendImageToProps(& $props, $image, $format)
    {
        $contentType = static::getContentTypeForFormat($format);
        $props['format'] = \strtolower($format);
        $props['type'] = $contentType;
        if ($format === 'SVG') {
            // SVGs are valid UTF-8 and consume less space w/o base64
            //
            // $props['raw'] = "data:$contentType;charset=UTF-8,"
            $props['raw'] = "data:$contentType;utf8," . static::prepareSvgDataString($image);
        } else {
            $props['raw'] = "data:$contentType;base64," . \base64_encode($image);
        }
    }

    public static function getContentTypeForFormat($format)
    {
        switch ($format) {
            case 'SVG':
                return 'image/svg+xml';
            case 'PNG':
                return 'image/png';
            default:
                throw new InvalidArgumentException(sprintf(
                    'RrdGraph format %s is not supported',
                    $format
                ));
        }
    }

    /**
     * @param string $image
     * @return array
     */
    public static function parseRawImage($image)
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

        $props = [
            'print' => [],
            'headerLength' => 0,
            'imageSize' => 0,
        ];
        $pos = 0;
        $blobSize = null;
        while ($blobSize === null) {
            $newLine = \strpos($image, "\n", $pos);
            if ($newLine === false) {
                throw new RuntimeException(
                    "Unable to parse rrdgraph info, there is no more newline after char #$pos"
                );
            }
            $line = \substr($image, $pos, $newLine - $pos);
            $pos = $newLine + 1;
            if (\preg_match('/^([a-z_]+)\s=\s(.+)$/', $line, $match)) {
                $key = $match[1];
                if ($key === 'image') {
                    // image = BLOB_SIZE:1229123
                    $blobSize = (int)\preg_replace('/^BLOB_SIZE:/', '', $match[2]);
                    break;
                } else {
                    list($ns, $relKey) = \preg_split('/_/', $key, 2);
                }
                switch ($ns) {
                    case 'graph':
                    case 'image':
                        $value = (int)$match[2];
                        break;
                    case 'value':
                        $value = static::parseLocalizedFloat($match[2]);
                        break;
                    default:
                        $value = (string)$match[2];
                }
                $props[$ns][$relKey] = $value;
            } elseif (\preg_match('/^print\[(\d+)]\s=\s(.+)$/', $line, $match)) {
                $key = $match[1];
                $value = $match[2];
                // TODO: what about INTs?
                if (\preg_match('/^"?(\d+)([,.]\d+)?"$/', $value, $match)) {
                    if (isset($match[2])) {
                        $value = static::parseLocalizedFloat($match[1] . $match[2]);
                    } else {
                        $value = (int) $match[1];
                    }
                }
                $props['print'][$key] = $value;
            } elseif (/*$pos === 0 &&*/ preg_match('/^OK /', $line)) {
                $props['ok_line'] = $line;
                return $props;
            } else {
\Icinga\Application\Logger::error($image);
                throw new RuntimeException("Unable to parse rrdgraph info line: '$line'");
            }
        }
        $props['headerLength'] = $pos;
        $props['imageSize'] = $blobSize;

        return $props;
    }

    public static function parseLocalizedFloat($string)
    {
        return (float) \str_replace(',', '.', $string);
    }

    /**
     * Removes newlines, quotes single quotes, then replaces double with single
     * quotes and finally only escapes a very few essential characters (<, >, #)
     *
     * @param string $svg
     * @return string
     */
    protected static function prepareSvgDataString($svg)
    {
        return str_replace([
            "\r",
            "\n",
            "'",
            '"',
            '<',
            '>',
            '#',
        ], [
            '',
            '',
            '%27', // urlencode("'"),
            "'",
            '%3C', // urlencode('<'),
            '%3E', // urlencode('>'),
            '%23', // urlencode('#'),
        ], $svg);
    }
}
