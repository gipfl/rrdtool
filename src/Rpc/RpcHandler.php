<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Error;
use gipfl\Protocol\JsonRpc\Notification;
use gipfl\Protocol\JsonRpc\PacketHandler;
use gipfl\Protocol\JsonRpc\Request;
use gipfl\RrdTool\RrdCached\Client;
use gipfl\RrdTool\RrdGraphInfo;
use gipfl\RrdTool\RrdSummary;
use gipfl\RrdTool\Rrdtool;
use function React\Promise\all;
use function React\Promise\resolve;

class RpcHandler implements PacketHandler
{
    protected $rrdtool;

    /** @var Client */
    protected $client;

    public function __construct(Rrdtool $rrdtool, Client $client)
    {
        $this->rrdtool = $rrdtool;
        $this->client = $client;
    }

    public function handle(Notification $packet)
    {
        if ($packet instanceof Request) {
            switch ($packet->getMethod()) {
                case 'rrdtool.version':
                    return 'v0.1.0';
                case 'rrdtool.graph':
                    return $this->prepareRpnGraph($packet);
                case 'rrdtool.recreate':
                    return $this->recreate($packet);
                case 'rrdtool.tune':
                    return $this->tune($packet);
                case 'rrdtool.flush':
                    return $this->client->flush($packet->getParam('file'));
                case 'rrdtool.forget':
                    return $this->client->forget($packet->getParam('file'));
                case 'rrdtool.flushAndForget':
                    return $this->client->flushAndForget($packet->getParam('file'));
                case 'rrdtool.info':
                    return $this->client->info($packet->getParam('file'));
                case 'rrdtool.rawinfo':
                    return $this->client->rawInfo($packet->getParam('file'));
                case 'rrdtool.pending':
                    return $this->client->pending($packet->getParam('file'));
//                case 'rrdtool.last':
//                    return $this->client->last($packet->getParam('file'));
                case 'rrdtool.first':
                    return $this->client->first($packet->getParam('file'), $packet->getParam('rra'));
                case 'rrdtool.calculate':
                    return $this->calculate($packet);
                case 'rrdtool.last':
                    return $this->last($packet);
                case 'rrdtool.listCommands':
                    return $this->client->listAvailableCommands();
                case 'rrdtool.hasCommand':
                    return $this->client->hasCommand($packet->getParam('command'));
                case 'rrdtool.listRecursive':
                    return $this->client->hasCommand('LIST')->then(function ($hasList) {
                        if ($hasList) {
                            return $this->client->listRecursive();
                        } else {
                            $basedir = $this->rrdtool->getBasedir();
                            $prefixLen = \strlen($basedir) + 1;
                            return resolve(\array_map(static function ($file) use ($prefixLen) {
                                return \substr($file, $prefixLen);
                            }, \glob($basedir . '/**/*.rrd')));
                        }
                    });
                default:
                    return new Error(Error::METHOD_NOT_FOUND);
            }
        } else {
            return null;
        }
    }

    public function tune(Request $packet)
    {
        $rrdtool = $this->rrdtool;
        $file = $packet->getParam('filename');
        $tuning = $packet->getParam('tuning');

        $rrdtool->run("tune $file $tuning", false);
        if ($rrdtool->hasError()) {
            throw new \RuntimeException($rrdtool->getError());
        }

        // string(25) "OK u:0,24 s:0,00 r:31,71 "
        return $rrdtool->getStdout();
    }

    public function recreate(Request $packet)
    {
        $rrdtool = $this->rrdtool;
        $file = $packet->getParam('filename');

        return $rrdtool->recreateFile($file, true);
    }

    public function prepareRpnGraph(Request $packet)
    {
        $rrdtool = $this->rrdtool;
        // Used to be $this->graph->getFormat()
        $format = $packet->getParam('format'); // TODO: this duplicates command string
        $command = $packet->getParam('command');

        $rrdtool->run($command, false);
        if ($rrdtool->hasError()) {
            throw new \RuntimeException($rrdtool->getError());
        }
        $image = $rrdtool->getStdout();

        $info = RrdGraphInfo::parseRawImage($image);
        // $info['print'] = $graph->translatePrintLabels($info['print']);
        $imageString = \substr($image, $info['headerLength']);
        RrdGraphInfo::appendImageToProps($props, $imageString, $format);

        return $props;
    }

    protected function last(Request $packet)
    {
        if ($file = $packet->getParam('file')) {
            return $this->client->last($file);
        } else {
            $files = $packet->getParam('files');
            $result = [];
            foreach ($files as $file) {
                $result[$file] = $this->client->last("$file.rrd")->then(function ($result) {
                    var_dump('Success', $result);
                }, function ($e) {
                    var_dump('Err', $e);
                });
            }

            return all($result);
        }
    }

    protected function calculate(Request $packet)
    {
        $files = $packet->getParam('files');
        $dsNames = $packet->getParam('ds');
        $ds = [];
        foreach ($files as $file) {
            foreach ($dsNames as $name) {
                $ds[] = (object)[
                    'filename'   => $file,
                    'datasource' => $name,
                ];
            }
        }
        $start = $packet->getParam('start');
        $end = $packet->getParam('end');

        $summary = new RrdSummary($this->rrdtool);

        return $summary->summariesForDatasources($ds, $start, $end);
    }
}
