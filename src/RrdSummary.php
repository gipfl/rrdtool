<?php

namespace gipfl\RrdTool;

class RrdSummary
{
    protected $rrd;

    protected $aggregateMethods = [
        'max'   => ['MAX', 'MAXIMUM'],
        'min'   => ['MIN', 'MINIMUM'],
        'avg'   => ['AVERAGE', 'AVERAGE'],
        'stdev' => ['AVERAGE', 'STDEV'],
    ];

    public function __construct(Rrdtool $rrd)
    {
        $this->rrd = $rrd;
    }

    public function getPattern()
    {
        $pattern = [];
        foreach ($this->aggregateMethods as $alias => $funcs) {
            $pattern[$alias] = ' DEF:%3$sa=%1$s:%2$s:'
                . $funcs[0]
                . ' VDEF:%3$saa=%3$sa,'
                . $funcs[1]
                . ' PRINT:%3$saa:"%4$d %5$s %%.4lf"';
        }

        return $pattern;
    }

    public function summariesForDatasources($datasources, $start, $end, $dayColumn = null)
    {
        $pattern = $this->getPattern();
        $cmds = [];

        $baseCmd = 'graph /dev/null -f "" --start ' . $start . ' --end ' . $end;
        $cmd = $baseCmd;
        $cnt = 0;

        foreach ($datasources as $idx => $ds) {
            /*
            if (! file_exists($this->basedir . '/' . $ds->filename)) {
                // TODO: Should we fail here?
                continue;
            }
            */

            foreach ($pattern as $name => $rpn) {
                $prefix = $name . $idx;
                $cmd .= sprintf(
                    $rpn,
                    $this->string($ds->filename),
                    $this->string($ds->datasource),
                    $prefix,
                    $idx,
                    $name
                );
            }

            $cnt++;
            if ($cnt > 100) {
                $cmds[] = $cmd;
                $cmd = $baseCmd;
                $cnt = 0;
            }
        }
        if ($cnt !== 0) {
            $cmds[] = $cmd;
        }

        $rrd = $this->rrd;
        $res = [];
        if ($dayColumn !== null) {
            $dayColumn = date('Y-m-d', $start);
        }

        foreach ($rrd->runBulk($cmds) as $key => $stdout) {
            if ($stdout === false) {
                // printf("%s failed\n", $cmds[$key]);
            } else {
                // printf("%s SUCCEEDED\n", $cmds[$key]);
                // echo $stdout;

                foreach (preg_split('/\n/', $stdout, -1, PREG_SPLIT_NO_EMPTY) as $line) {
                    list($dsid, $what, $value) = preg_split('/ /', $line, 3);
                    if (!is_numeric($dsid)) {
                        // TODO: Should we fail here?
                        echo $line . "\n";
                        continue;
                    }

                    $filename = $datasources[$dsid]->filename;
                    $dsname = $datasources[$dsid]->datasource;
                    if (! isset($res[$filename][$dsname])) {
                        $res[$filename][$dsname] = [];
                    }

                    // TODO: What about inf/-inf?
                    if (strtolower($value) === 'nan' || strtolower($value) === '-nan') {
                        $value = null;
                    }

                    // This is localized :-/
                    $res[$filename][$dsname][$what . '_value'] = (float) str_replace(',', '.', $value);
                }
            }
        }

        return $res;
    }

    protected function string($string)
    {
        // TODO: Check and fix
        return "'" . addcslashes($string, "':") . "'";
    }
}
