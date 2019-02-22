<?php

namespace gipfl\RrdTool;

// TODO -b (--base) 1024 VS 1000 (traffic is 1000, memory 1024)
// = do not fail for missing RRD or DS

use gipfl\RrdTool\Graph\Area;
use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Instruction;
use gipfl\RrdTool\Graph\Line;

class RrdGraph
{
    /** @var int */
    protected $width = 840;

    /** @var int */
    protected $height= 300;

    /** @var int */
    protected $start;

    /** @var int */
    protected $end;

    protected $format = 'PNG';

    protected $instructions = [];

    protected $onlyGraph;

    protected $title;

    protected $defs = [];

    protected $cdefs = [];

    protected $vdefs = [];

    protected $usedAliases = [];

    protected $printLabels = [];

    /** @var int */
    protected $border = 0;

    /** @var bool */
    protected $fullSizeMode = true;

    /** @var string 1:0 = lines */
    protected $gridDash = '3:0';

    /** @var string  numeric, timestamp, duration */
    protected $leftAxisFormatter = 'numeric';

    // 10 * 1024 * 1024 * 1024;
    // 1 * 1024 * 1024 * 1024;
    // 100 * 1024 * 1024;
    /** @var int */
    protected $upperLimit;

    /** @var int */
    protected $lowerLimit;

    /** @var bool */
    protected $rigid = false;

    /** @var bool */
    protected $useNanForAllMissingData = false;

    /** @var bool */
    protected $enableRrdToolTag = false;

    /** @var string|null */
    protected $watermark;

    /** @var bool */
    protected $slopeMode = true;

    /** @var Color|null */
    protected $textColor;

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     * @return RrdGraph
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param int $end
     * @return RrdGraph
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    public function def($filename, $ds, $cf)
    {
        $filename = $this->string($filename);
        $quotedDs = $this->string($ds);
        $def = "$filename:$quotedDs:$cf";
        if (isset($this->defs[$def])) {
            return $this->defs[$def];
        }
        $alias = $this->getUniqueAlias('def_' . strtolower($cf) . '_' . $ds);
        $this->defs[$def] = $alias;

        return $alias;
    }

    public function cdef($expression, $preferredAlias = null)
    {
        if (isset($this->cdefs[$expression])) {
            return $this->cdefs[$expression];
        }
        if ($preferredAlias === null) {
            $preferredAlias = 'cdef__1';
        }
        $alias = $this->getUniqueAlias($preferredAlias);
        $this->cdefs[$expression] = $alias;

        return $alias;
    }

    public function vdef($expression, $preferredAlias = null)
    {
        if (isset($this->vdefs[$expression])) {
            return $this->vdefs[$expression];
        }
        if ($preferredAlias === null) {
            $preferredAlias = 'vdef__1';
        }
        $alias = $this->getUniqueAlias($preferredAlias);
        $this->vdefs[$expression] = $alias;

        return $alias;
    }

    public function printDef($def, $format, $name = null)
    {
        $this->instructions[] = "PRINT:$def:$format";
        $this->printLabels[] = $name === null ? $def : $name;
    }

    public function getPrintLabel($num)
    {
        $num = (int) $num;
        if (array_key_exists($num, $this->printLabels)) {
            return $this->printLabels[$num];
        } else {
            return $num;
        }
    }

    protected function getUniqueAlias($name)
    {
        while (isset($this->usedAliases[$name])) {
            $name = $this->makeNextName($name);
        }

        $this->usedAliases[$name] = true;

        return $name;
    }

    protected function makeNextName($name)
    {
        if (preg_match('/^(.+__)(\d+)$/', $name, $match)) {
            return $match[1] . (string) ((int) $match[2] + 1);
        } else {
            return $name . '__2';
        }
    }

    /**
     * PNG|SVG|EPS|PDF|XML|XMLENUM|JSON|JSONTIME|CSV|TSV|SSV
     *
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = strtoupper($format);

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return Color
     */
    public function getTextColor()
    {
        if ($this->textColor === null) {
            return new Color('#535353');
        } else {
            return $this->textColor;
        }
    }

    /**
     * @param Color|string $textColor
     * @return $this
     */
    public function setTextColor($textColor)
    {
        $this->textColor = new Color($textColor);

        return $this;
    }

    public function setOnlyGraph($onlyGraph = true)
    {
        $this->onlyGraph = $onlyGraph;
        return $this;
    }

    protected function drawOnlyGraph()
    {
        if ($this->onlyGraph !== null) {
            return $this->onlyGraph;
        }

//        return $this->height < 30;
        return $this->height < 120;
    }

    public function add($instruction)
    {
        if ($instruction instanceof Instruction) {
            $this->instructions[] = $instruction;
        } elseif (is_array($instruction)) {
            foreach ($instruction as $i) {
                $this->add($i);
            }
        } else {
            throw new \InvalidArgumentException('Expected instruction(s)');
        }

        return $this;
    }

    public function area($def, $color = null, $stack = false, $legend = '')
    {
        $color = new Color($color);
        $legend = $this->optionalString($legend);
        if ($stack) {
            $this->add((new Area($def, $color->setAlphaHex('cc'), $legend))->setStack());
        } else {
            $this->add([
                new Line($def, $color, $legend),
                new Area($def, $color->setAlphaHex('66'))
            ]);
        }
    }

    public function line1($def, $color = null, $legend = '')
    {
        $this->add(new Line($def, $color, $legend));
    }

    public function addPacketLoss($file, $dsname, $color = 'ff5566')
    {
        if ($file instanceof RrdFile) {
            $file = $file->getFilename();
        }
        $max = $this->def($file, $dsname, 'MAX');
        $ploss = $this->cdef("$max,100,0,IF");
        $this->instructions[] = "AREA${ploss}#${color}:skipscale";
    }

    public function addWarningRule($value, $legend = '', $color = 'ffaa44')
    {
        return $this->addHRule($value, $color, $legend);
    }

    public function addCriticalRule($value, $legend = '', $color = 'ff5566')
    {
        return $this->addHRule($value, $color, $legend);
    }

    public function addHRule($value, $color, $legend = '')
    {
        $this->instructions[] = "HRULE:${value}#${color}" . $this->optionalString($legend) . ':dashes=3,5';
        return $this;
    }

    protected function optionalString($string)
    {
        if (empty($string)) {
            return '';
        } else {
            return ':' . $this->string($string);
        }
    }

    protected function string($string)
    {
        // TODO: Check and fix
        return "'" . addcslashes($string, "':") . "'";
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @param bool $useNan
     * @return $this
     */
    public function setUseNanForAllMissingData($useNan = true)
    {
        $this->useNanForAllMissingData = (bool) $useNan;

        return $this;
    }

    /**
     * @param bool $rigid
     * @return $this
     */
    public function setRigid($rigid = true)
    {
        $this->rigid = (bool) $rigid;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRrdToolTagEnabled()
    {
        return $this->enableRrdToolTag;
    }

    /**
     * @param bool $enableRrdToolTag
     */
    public function enableRrdToolTag($enableRrdToolTag)
    {
        $this->enableRrdToolTag = $enableRrdToolTag;
    }

    /**
     * @return string|null
     */
    public function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * @param string|null $watermark
     * @return RrdGraph
     */
    public function setWatermark($watermark)
    {
        $this->watermark = $watermark;

        return $this;
    }

    public function getInstructions()
    {
        $instructions = [];
        foreach ($this->defs as $def => $alias) {
            $alias = $this->string($alias);
            $instructions[] = "DEF:$alias=$def";
        }
        foreach ($this->cdefs as $expression => $alias) {
            $alias = $this->string($alias);
            $instructions[] = "CDEF:$alias=$expression";
        }
        foreach ($this->vdefs as $expression => $alias) {
            $alias = $this->string($alias);
            $instructions[] = "VDEF:$alias=$expression";
        }

        return array_merge($instructions, $this->instructions);
    }

    protected function getMainParams()
    {
        $blue = '#0095BF'; // @icinga-blue
        $textColor = $this->getTextColor();
        // solarized-dark:
        // $blue = '586e75';
        // $textColor = 'e3e3e3';
        $gridFontSize = $this->height < 160 ? 6 : 7;
        $fontFamily = 'DejaVuSerif';
        $fontFamily = 'LiberationSerif';
        $fontFamily = 'LiberationSansMono';
        $params = [
            '--start'  => $this->start,
            '--end'    => $this->end,
            '--width'  => $this->width,
            '--height' => $this->height,
            '--title'  => $this->drawOnlyGraph() ? null : $this->stringOrNull($this->title),
            // '--vertical-label' =>  'verti',
            // '-S' => 10,
            '--border' => $this->getBorder(),
            '--full-size-mode' => $this->isFullSizeMode(),
            // '--logarithmic',
            '--no-legend',
            // '--legend-position west',
            // '--dynamic-labels',
            // '--force-rules-legend',
            // '--right-axis 1:0',
            // '--daemon' => '127.0.0.1:...', // triggers flush
            '--color BACK#ffffff00',
            "--color CANVAS${blue}00",
            "--color GRID${blue}10",
            "--color MGRID${blue}33",
            "--color ARROW${blue}00",
            "--color AXIS${blue}00",
            "--color FONT${textColor}",
            '--grid-dash' => $this->gridDash,
            '--slope-mode' => $this->slopeMode,
            '--disable-rrdtool-tag' => ! $this->isRrdToolTagEnabled(),
            //'--font DEFAULT:0:UbuntuMono --font AXIS:7:UbuntuMono',
            // "--font DEFAULT:0:UbuntuMono --font AXIS:$gridFontSize:UbuntuMono",
            "--font DEFAULT:0:$fontFamily --font AXIS:$gridFontSize:$fontFamily",
            // '--font-render-mode' => 'light', // light, mono
            // '-y none',
            // '-x none',
            // '--zoom' => 4,
            '--watermark' => $this->getWatermark(),
            '--imgformat' => $this->getFormat(),
            // '-n AXIS:5',
            '--units-length' => '4',
            '--rigid' => $this->rigid,
            // '--autoscale',
            // '--alt-autoscale',
            // '--no-gridfit',
            // '--x-grid none',
            // '--x-grid MINUTE:10:HOUR:1:HOUR:4:0:%X',
            // '--x-grid HOUR:8:DAY:1:DAY:1:86400:%A',
            '--left-axis-formatter' => $this->leftAxisFormatter,
            "--week-fmt 'KW %W'", // TODO: translate? %V, %U
            // '--base 1024', // memory
            // '--base 1000', // traffic
            '--lower-limit' => $this->getLowerLimit(),
            '--upper-limit' => $this->getUpperLimit(),
            '-Z' => $this->useNanForAllMissingData, // Do not fail on missing DS
        ];

        if ($this->drawOnlyGraph()) {
             $params[] = '--only-graph';
             $params[] = '--color CANVAS#ffffff00';
        }

        return $params;
    }

    protected function stringOrNull($string)
    {
        if ($string === null || strlen($string) === 0) {
            return null;
        }

        return $this->string($string);
    }

    /**
     * @return int
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     * @param int $border
     * @return RrdGraph
     */
    public function setBorder($border)
    {
        $this->border = $border;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFullSizeMode()
    {
        return $this->fullSizeMode;
    }

    /**
     * @param bool $fullSizeMode
     * @return RrdGraph
     */
    public function setFullSizeMode($fullSizeMode)
    {
        $this->fullSizeMode = $fullSizeMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLeftAxisFormatter()
    {
        return $this->leftAxisFormatter;
    }

    /**
     * @param string $leftAxisFormatter
     * @return RrdGraph
     */
    public function setLeftAxisFormatter($leftAxisFormatter)
    {
        $this->leftAxisFormatter = $leftAxisFormatter;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpperLimit()
    {
        return $this->upperLimit;
    }

    /**
     * @param int $upperLimit
     * @return RrdGraph
     */
    public function setUpperLimit($upperLimit)
    {
        $this->upperLimit = $upperLimit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLowerLimit()
    {
        return $this->lowerLimit;
    }

    /**
     * @param int $lowerLimit
     * @return RrdGraph
     */
    public function setLowerLimit($lowerLimit)
    {
        $this->lowerLimit = $lowerLimit;

        return $this;
    }

    protected function joinParams($params)
    {
        $str = '';
        foreach ($params as $k => $v) {
            if ($v === null || $v === false) {
                continue;
            }
            if (is_int($k)) {
                $str .= " $v";
            } else {
                if ($v === true) {
                    $str .= " $k";
                } else {
                    $str .= " $k $v";
                }
            }
        }

        return $str;
    }

    public function getRaw(Rrdtool $rrdtool, $withDetails = false)
    {
        $rrdtool->run($this->getCommandString($withDetails));
        if ($rrdtool->hasError()) {
            throw new \RuntimeException($rrdtool->getError() . ' ('. $this->getCommandString($withDetails) .')');
        }

        $out = $rrdtool->getStdout();
        // This is localized!
        // OK u:0,02 s:0,00 r:0,01
        // OK u:0.02 s:0.00 r:0.01
        return preg_replace('/OK\su:[0-9\.,]+\ss:[0-9\.,]+\sr:[0-9\.,]+\n$/', '', $out);
    }

    public function dump(Rrdtool $rrdtool, $withDetails = false)
    {
        echo $this->getRaw($rrdtool, $withDetails);
    }

    public function dumpWithDetails(Rrdtool $rrdtool)
    {
        $rrdtool->run($this->getCommandString(true));
        echo $rrdtool->getStdout();
    }

    public function getCommandString($verbose = false)
    {
        $cmd = $verbose ? 'graphv' : 'graph';
        // graphv gives:
        // graph_left = 83
        // graph_top = 15
        // graph_width = 742
        // graph_height = 288
        // image_width = 840
        // image_height = 320
        // graph_start = 1493928095
        // graph_end = 1493942495
        // value_min = 0,0000000000e+00
        // value_max = 1,4626943333e+00
        // image = BLOB_SIZE:103461

        return "$cmd -"
            . $this->joinParams($this->getMainParams())
            . ' ' . implode(' ', $this->getInstructions());
    }
}
