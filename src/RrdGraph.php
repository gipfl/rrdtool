<?php

namespace gipfl\RrdTool;

// TODO -b (--base) 1024 VS 1000 (traffic is 1000, memory 1024)
// = do not fail for missing RRD or DS

use gipfl\RrdTool\Graph\Color;
use gipfl\RrdTool\Graph\Instruction\Area;
use gipfl\RrdTool\Graph\Instruction\HRule;
use gipfl\RrdTool\Graph\Instruction\Instruction;
use gipfl\RrdTool\Graph\Instruction\Line;
use gipfl\RrdTool\Graph\Instruction\PrintInstruction;

class RrdGraph
{
    protected int $width = 840;
    protected int $height= 300;
    protected int $start;
    protected int $end;
    protected string $format = 'PNG';

    /** @var Instruction[] */
    protected array $instructions = [];
    protected ?bool $onlyGraph = null;
    protected ?string $title = null;
    /** @var string[] */
    protected array $defs = [];
    /** @var string[] */
    protected array $cdefs = [];
    /** @var string[] */
    protected array $vdefs = [];
    /** @var string[] */
    protected array $usedAliases = [];
    protected array $printLabels = [];
    protected int $border = 0;
    protected bool $fullSizeMode = true;

    /** @var string 1:0 = lines */
    protected string $gridDash = '3:0';

    /** @var string  numeric, timestamp, duration */
    protected string $leftAxisFormatter = 'numeric';

    // 10 * 1024 * 1024 * 1024;
    // 1 * 1024 * 1024 * 1024;
    // 100 * 1024 * 1024;
    protected ?int $upperLimit = null;
    protected ?int $lowerLimit = null;
    protected bool $rigid = false;
    protected bool $useNanForAllMissingData = false;
    protected bool $enableRrdToolTag = false;
    protected ?string $watermark = null;
    protected bool $slopeMode = false;
    protected ?Color $textColor = null;
    protected bool $disableCached = false;
    protected ?int $step = null;
    protected bool $darkTheme = false;

    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start
     * @return RrdGraph
     */
    public function setStart($start)
    {
        // 1990
        if ($start < 631152000) {
            throw new \RuntimeException(sprintf(
                "Beginning a graph on %s (%s) seems not plausible",
                @date('Y-m-d H:i:s', $start),
                $start
            ));
        }
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

    public function setEnd(int $end): RrdGraph
    {
        // 2030
        if ($end > 1893456000) {
            throw new \RuntimeException(sprintf(
                "Ending a graph on %s (%s) seems not plausible",
                @date('Y-m-d H:i:s', $end),
                $end
            ));
        }
        $this->end = $end;
        return $this;
    }

    public function setStep($step)
    {
        $this->step = $step;
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
        $this->instructions[] = new PrintInstruction($def, $format);
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

    public function translatePrintLabels(array $labels)
    {
        $result = [];
        foreach ($labels as $key => $value) {
            $result[$this->getPrintLabel($key)] = $value;
        }

        return $result;
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

        return $this->height < 80;
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
        $this->instructions[] = (new HRule($value, $color, $legend))->setDashes('3,5');
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
        if ($string === null) {
            return "''";
        }
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

    public function disableCached($disable = true)
    {
        $this->disableCached = $disable;

        return $this;
    }

    /**
     * @param bool $darkTheme
     * @return $this
     */
    public function setDarkTheme($darkTheme = true)
    {
        $this->darkTheme = $darkTheme;

        return $this;
    }

    protected function getMainParams()
    {
        // TODO: Combine with setTextColor()
        if ($this->darkTheme) {
            $blue = '#586e75';
            $textColor = '#e3e3e3';
        } else {
            $blue = '#0095BF'; // @icinga-blue
            $textColor = $this->getTextColor();
        }
        $gridFontSize = $this->height < 160 ? 6 : 7;
        if ($this->width < 100) {
            $gridFontSize = 4;
        }
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
            '--grid-dash' => $this->gridDash,
            '--slope-mode' => $this->slopeMode,
            '--disable-rrdtool-tag' => ! $this->isRrdToolTagEnabled(),
            //'--font DEFAULT:0:UbuntuMono --font AXIS:7:UbuntuMono',
            // "--font DEFAULT:0:UbuntuMono --font AXIS:$gridFontSize:UbuntuMono",
            "--font DEFAULT:0:$fontFamily --font AXIS:$gridFontSize:$fontFamily",
            // '--font-render-mode' => 'light', // normal, light, mono
            // '-y none',
            // '-x none',
            '--zoom' => 1,
            '--watermark' => $this->getWatermark(),
            '--imgformat' => $this->getFormat(),
            // '-n AXIS:5',
            '--rigid' => $this->rigid,
            // '--autoscale',
            // '--alt-autoscale',
            // '--no-gridfit',
            // '--x-grid none',
            // '--x-grid MINUTE:10:HOUR:1:HOUR:4:0:%X',
            // '--x-grid HOUR:8:DAY:1:DAY:1:86400:%A',
            '--units-length' => '4', // Space on the left side, reserved for Y axis
            // '--units=si',
            // '-y none',
            '--left-axis-formatter' => $this->leftAxisFormatter,
            "--week-fmt 'KW %W'", // TODO: translate? %V, %U
            // '--base 1024', // memory
            // '--base 1000', // traffic

            '--lower-limit' => $this->getLowerLimit(),
            '--upper-limit' => $this->getUpperLimit(),
            '-Z' => $this->useNanForAllMissingData, // Do not fail on missing DS
            '--step' => $this->step,
        ];

        $colors = [
            'BACK'   => new Color('#ffffff00'),
            'CANVAS' => new Color($this->drawOnlyGraph() ? '#ffffff' : $blue, '00'), // Is this required?
            'GRID'   => new Color($blue, '00'),
            'MGRID'  => new Color($blue, '33'),
            'ARROW'  => new Color($blue, '00'),
            'AXIS'   => new Color($blue, '00'),
            'FONT'   => new Color($textColor),
        ];
        foreach ($colors as $target => $color) {
            $params[] = "--color $target$color";
        }

        if ($this->drawOnlyGraph()) {
             $params[] = '--only-graph';
        }

        if ($this->disableCached) {
            $params['--daemon'] = "''";
        }

        return $params;
    }

    protected function stringOrNull($string): ?string
    {
        if ($string === null || strlen($string) === 0) {
            return null;
        }

        return $this->string($string);
    }

    public function getBorder(): int
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

        return $rrdtool->getStdout();
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
