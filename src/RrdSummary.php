<?php

namespace gipfl\RrdTool;

use React\Promise\ExtendedPromiseInterface;

class RrdSummary
{
    /** @var AsyncRrdtool */
    protected $rrd;

    protected $aggregateMethods = [
        'max'   => ['MAX', 'MAXIMUM'],
        'min'   => ['MIN', 'MINIMUM'],
        'avg'   => ['AVERAGE', 'AVERAGE'],
        'maxavg' => ['AVERAGE', 'MAXIMUM'],
        'stdev' => ['AVERAGE', 'STDEV'],
    ];

    public function __construct(AsyncRrdtool $rrd)
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

    public function summariesForDatasources($datasources, $start, $end, $dayColumn = null): ExtendedPromiseInterface
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

        if ($dayColumn !== null) {
            $dayColumn = date('Y-m-d', $start);
        }

        return $this->rrd->sendMany($cmds)->then(function ($result) use ($datasources, $dayColumn) {
            $res = [];
            foreach ($result as $key => $stdout) {
                if ($stdout === false) {
                    // printf("%s failed\n", $cmds[$key]);
                } else {
                    // printf("%s SUCCEEDED\n", $cmds[$key]);
                    // echo $stdout;

                    foreach (preg_split('/\n/', $stdout, -1, PREG_SPLIT_NO_EMPTY) as $line) {
                        if (count(preg_split('/ /', $line)) < 3) {
                            var_dump($line);
                            die();
                        }
                        try {
                            list($dsid, $what, $value) = preg_split('/ /', $line, 3);
                        } catch (\Exception $e) {
                            var_dump($line);
                            throw $e;
                        }
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
                        if ($value === null) {
                            $res[$filename][$dsname][$what . '_value'] = null;
                        } else {
                            $res[$filename][$dsname][$what . '_value'] = (float) str_replace(',', '.', $value);
                        }
                    }
                }
            }

            return $res;
        });
    }

    protected function string($string)
    {
        // TODO: Check and fix
        return "'" . addcslashes($string, "':") . "'";
    }
}
