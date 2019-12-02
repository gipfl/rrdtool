<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Error;
use gipfl\Protocol\JsonRpc\Notification;
use gipfl\Protocol\JsonRpc\PacketHandler;
use gipfl\Protocol\JsonRpc\Request;
use gipfl\Protocol\JsonRpc\Response;
use gipfl\RrdTool\Graph\Instruction\Shift;
use gipfl\RrdTool\GraphTemplate\TemplateLoader;
use gipfl\RrdTool\RrdGraph;
use gipfl\RrdTool\RrdGraphInfo;
use gipfl\RrdTool\RrdSummary;
use gipfl\RrdTool\Rrdtool;

class RpcHandler implements PacketHandler
{
    protected $rrdtool;

    public function __construct(Rrdtool $rrdtool)
    {
        $this->rrdtool = $rrdtool;
    }

    public function handle(Notification $packet)
    {
        if ($packet instanceof Request) {
            switch ($packet->getMethod()) {
                case 'rrdtool.version':
                    return 'v0.1.0';
                case 'rrdtool.graph':
                    return $this->prepareRpnGraph($packet);
                case 'rrdtool.calculate':
                    return $this->calculate($packet);
                default:
                    return new Error(Error::METHOD_NOT_FOUND);
            }
        } else {
            return null;
        }
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
